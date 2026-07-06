<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Asset;
use App\Models\Debt;

class DebtObserver
{
    public function saved(Debt $debt): void
    {
        $this->syncLinkedAsset($debt);
    }

    public function deleted(Debt $debt): void
    {
        $this->deleteLinkedAsset($debt);
    }

    private function syncLinkedAsset(Debt $debt): void
    {
        if (! $this->isPurchaseType((string) $debt->type)) {
            $this->deleteLinkedAsset($debt);

            return;
        }

        $details = (array) $debt->details;
        $assetType = $this->assetTypeForDebt($debt);
        $assetName = $this->assetNameForDebt($debt);
        $purchasePrice = max(0, (float) $debt->original_amount);
        $purchaseDate = $debt->start_date ?? now();
        $currentValue = $purchasePrice;
        $serialNumber = $this->linkedAssetSerial($debt);

        $asset = Asset::withTrashed()
            ->where('user_id', $debt->user_id)
            ->where('serial_number', $serialNumber)
            ->first();

        $payload = [
            'user_id' => $debt->user_id,
            'name' => $assetName,
            'type' => $assetType,
            'purchase_price' => $purchasePrice,
            'purchase_date' => $purchaseDate,
            'current_value' => $currentValue,
            'depreciation_rate' => 0,
            'serial_number' => $serialNumber,
            'notes' => 'Auto-linked from debt #' . $debt->id,
        ];

        if ($asset) {
            if ($asset->trashed()) {
                $asset->restore();
            }

            $asset->fill($payload)->saveQuietly();

            return;
        }

        Asset::query()->create($payload);
    }

    private function deleteLinkedAsset(Debt $debt): void
    {
        Asset::query()
            ->where('user_id', $debt->user_id)
            ->where('serial_number', $this->linkedAssetSerial($debt))
            ->delete();
    }

    private function linkedAssetSerial(Debt $debt): string
    {
        return 'DEBT-LINK-' . $debt->id;
    }

    private function isPurchaseType(string $type): bool
    {
        return in_array($type, ['hire_purchase', 'vehicle_loan', 'mortgage'], true);
    }

    private function assetNameForDebt(Debt $debt): string
    {
        $details = (array) $debt->details;
        $itemName = trim((string) ($details['item_name'] ?? ''));

        if ($itemName !== '') {
            return $itemName;
        }

        return match ((string) $debt->type) {
            'mortgage' => 'Property Purchase',
            'vehicle_loan' => 'Vehicle Purchase',
            default => 'Purchased Asset',
        };
    }

    private function assetTypeForDebt(Debt $debt): string
    {
        $type = (string) $debt->type;
        $details = (array) $debt->details;
        $itemName = strtolower(trim((string) ($details['item_name'] ?? '')));

        if ($type === 'mortgage') {
            return 'building';
        }

        if ($type === 'vehicle_loan') {
            return 'vehicle';
        }

        if ($itemName === '') {
            return 'other';
        }

        if (str_contains($itemName, 'car') || str_contains($itemName, 'truck') || str_contains($itemName, 'vehicle') || str_contains($itemName, 'bus') || str_contains($itemName, 'motorbike')) {
            return 'vehicle';
        }

        if (str_contains($itemName, 'house') || str_contains($itemName, 'land') || str_contains($itemName, 'plot') || str_contains($itemName, 'building') || str_contains($itemName, 'farm')) {
            return 'building';
        }

        if (str_contains($itemName, 'machine') || str_contains($itemName, 'generator')) {
            return 'machinery';
        }

        if (str_contains($itemName, 'laptop') || str_contains($itemName, 'phone') || str_contains($itemName, 'tv') || str_contains($itemName, 'computer')) {
            return 'electronics';
        }

        if (str_contains($itemName, 'desk') || str_contains($itemName, 'sofa') || str_contains($itemName, 'bed') || str_contains($itemName, 'table') || str_contains($itemName, 'chair')) {
            return 'furniture';
        }

        return 'other';
    }
}
