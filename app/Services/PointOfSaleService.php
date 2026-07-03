<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LedgerTransaction;
use App\Models\StockMovement;
use App\Services\Accounting\PostingRuleService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PointOfSaleService
{
    public function __construct(private readonly PostingRuleService $rules)
    {
    }

    /**
     * Ring up a cash sale: relieve stock, post revenue + COGS, optionally invoice.
     *
     * @param array{
     *     business_id:int, user_id:int, customer_id:int|null, date:string|null,
     *     items:array<int, array{inventory_id:int, quantity:int, unit_price:float|null}>,
     *     create_invoice:bool, notes:string|null
     * } $data
     * @return array{reference:string, total:float, cost:float, items:int}
     */
    public function checkout(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $businessId = (int) $data['business_id'];
            $userId     = (int) $data['user_id'];
            $date       = $data['date'] ?? now()->toDateString();

            $items = collect($data['items'] ?? [])
                ->filter(fn ($i) => (int) ($i['inventory_id'] ?? 0) > 0 && (int) ($i['quantity'] ?? 0) > 0)
                ->values();

            if ($items->isEmpty()) {
                throw new InvalidArgumentException('Add at least one line item to complete the sale.');
            }

            $products = Inventory::query()
                ->whereIn('id', $items->pluck('inventory_id'))
                ->where('business_id', $businessId)
                ->get()
                ->keyBy('id');

            foreach ($items as $line) {
                $product = $products[$line['inventory_id']] ?? null;

                if (! $product) {
                    throw new InvalidArgumentException('One of the selected products is not available.');
                }

                if ($product->quantity_on_hand < (int) $line['quantity']) {
                    throw new InvalidArgumentException(
                        "Insufficient stock for {$product->name} (have {$product->quantity_on_hand})."
                    );
                }
            }

            $reference   = $this->reference($businessId);
            $revenueTotal = 0.0;
            $costTotal    = 0.0;
            $invoiceLines = [];

            foreach ($items as $line) {
                $product = $products[$line['inventory_id']];
                $qty     = (int) $line['quantity'];
                $unit    = round((float) ($line['unit_price'] ?? $product->selling_price), 2);

                $revenueTotal += $unit * $qty;
                $costTotal    += (float) $product->cost_price * $qty;

                StockMovement::create([
                    'business_id'  => $businessId,
                    'inventory_id' => $product->id,
                    'user_id'      => $userId,
                    'type'         => 'sale',
                    'quantity'     => $qty,
                    'unit_cost'    => $product->cost_price,
                    'reference'    => $reference,
                    'notes'        => 'POS sale',
                ]);

                $invoiceLines[] = [
                    'inventory_id' => $product->id,
                    'description'  => $product->name,
                    'quantity'     => $qty,
                    'unit_price'   => $unit,
                    'total_price'  => round($unit * $qty, 2),
                ];
            }

            $revenueTotal = round($revenueTotal, 2);
            $costTotal    = round($costTotal, 2);

            $this->postRevenue($businessId, $userId, $date, $reference, $revenueTotal);
            $this->postCostOfSales($businessId, $userId, $date, $reference, $costTotal);

            if (! empty($data['create_invoice'])) {
                $customerId = $this->normalizeNullableInt($data['customer_id'] ?? null);

                $this->createInvoice(
                    $businessId,
                    $customerId,
                    $date,
                    $reference,
                    $invoiceLines,
                    $revenueTotal,
                );
            }

            return [
                'reference' => $reference,
                'total'     => $revenueTotal,
                'cost'      => $costTotal,
                'items'     => $items->count(),
            ];
        });
    }

    private function postRevenue(int $businessId, int $userId, string $date, string $reference, float $total): void
    {
        $sales = $this->rules->accountByCode($businessId, '4100');

        if (! $sales || $total <= 0) {
            return;
        }

        LedgerTransaction::create([
            'business_id'    => $businessId,
            'user_id'        => $userId,
            'account_id'     => $sales->id,
            'amount'         => $total,
            'date'           => $date,
            'description'    => 'POS sale ' . $reference,
            'payment_status' => 'paid',
            'metadata'       => [
                'transaction_type' => 'money_in',
                'source'           => 'pos',
                'reference'        => $reference,
            ],
        ]);
    }

    private function postCostOfSales(int $businessId, int $userId, string $date, string $reference, float $cost): void
    {
        $cogs      = $this->rules->accountByCode($businessId, '5100');
        $inventory = $this->rules->accountByCode($businessId, '1200');

        if (! $cogs || ! $inventory || $cost <= 0) {
            return;
        }

        LedgerTransaction::create([
            'business_id'    => $businessId,
            'user_id'        => $userId,
            'account_id'     => $cogs->id,
            'amount'         => $cost,
            'date'           => $date,
            'description'    => 'Cost of goods sold ' . $reference,
            'payment_status' => 'paid',
            'metadata'       => [
                'transaction_type'   => 'money_out_direct',
                'counter_account_id' => $inventory->id,
                'source'             => 'pos',
                'reference'          => $reference,
            ],
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $lines
     */
    private function createInvoice(int $businessId, ?int $customerId, string $date, string $reference, array $lines, float $total): void
    {
        $invoice = Invoice::create([
            'business_id'     => $businessId,
            'customer_id'     => $customerId,
            'invoice_number'  => $reference,
            'type'            => 'receipt',
            'issue_date'      => $date,
            'due_date'        => $date,
            'subtotal'        => $total,
            'tax_amount'      => 0,
            'discount_amount' => 0,
            'total_amount'    => $total,
            'amount_paid'     => $total,
            'status'          => 'paid',
        ]);

        foreach ($lines as $line) {
            InvoiceItem::create(array_merge(['invoice_id' => $invoice->id], $line));
        }
    }

    private function reference(int $businessId): string
    {
        $year = now()->year;

        $count = StockMovement::query()
            ->where('business_id', $businessId)
            ->where('type', 'sale')
            ->whereYear('created_at', $year)
            ->whereNotNull('reference')
            ->distinct()
            ->count('reference');

        return sprintf('POS-%d-%04d', $year, $count + 1);
    }

    private function normalizeNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
