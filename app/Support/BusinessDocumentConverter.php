<?php

namespace App\Support;

use App\Models\Invoice;
use Illuminate\Support\Str;

class BusinessDocumentConverter
{
    /**
     * Number prefixes per document type.
     */
    private const PREFIXES = [
        'invoice'   => 'INV-',
        'quotation' => 'QTN-',
        'receipt'   => 'RCP-',
    ];

    /**
     * Default status per target document type.
     */
    private const STATUSES = [
        'invoice'   => 'draft',
        'quotation' => 'draft',
        'receipt'   => 'paid',
    ];

    /**
     * Convert a source document (e.g. quotation) into a new document of the
     * given target type (e.g. invoice), copying the header and all line items.
     *
     * @param  array<string, mixed>  $overrides  Header field overrides.
     */
    public static function convert(Invoice $source, string $targetType, array $overrides = []): Invoice
    {
        $target = $source->replicate([
            'invoice_number', 'converted_from_id', 'created_at', 'updated_at', 'deleted_at',
        ]);

        $target->type = $targetType;
        $target->invoice_number = self::generateNumber($targetType);
        $target->converted_from_id = $source->getKey();
        $target->status = self::STATUSES[$targetType] ?? 'draft';
        $target->issue_date = now();

        if ($targetType === 'invoice') {
            $target->amount_paid = 0;
        }

        foreach ($overrides as $key => $value) {
            $target->{$key} = $value;
        }

        $target->save();

        foreach ($source->items()->get() as $item) {
            $newItem = $item->replicate(['invoice_id', 'created_at', 'updated_at']);
            $newItem->invoice_id = $target->getKey();
            $newItem->save();
        }

        $target->subtotal = (float) $target->items()->sum('total_price');
        $target->total_amount = round(
            (float) $target->subtotal + (float) $target->tax_amount - (float) $target->discount_amount,
            2
        );

        // Keep receipt paid by default unless explicitly overridden.
        if ($targetType === 'receipt' && ! array_key_exists('amount_paid', $overrides)) {
            $target->amount_paid = $target->total_amount;
        }

        $target->save();

        return $target->refresh();
    }

    public static function generateNumber(string $type): string
    {
        $prefix = self::PREFIXES[$type] ?? 'DOC-';

        return $prefix . strtoupper(Str::random(6));
    }
}
