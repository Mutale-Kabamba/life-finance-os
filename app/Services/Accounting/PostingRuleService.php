<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\LedgerAccount;

class PostingRuleService
{
    /**
     * Settlement account for a business: code 1100, else first active asset by code.
     */
    public function settlementAccount(int $businessId): LedgerAccount
    {
        return LedgerAccount::query()
            ->where('business_id', $businessId)
            ->where('code', LedgerAccount::SETTLEMENT_CODE)
            ->first()
            ?? LedgerAccount::query()
                ->where('business_id', $businessId)
                ->where('type', 'asset')
                ->orderBy('code')
                ->firstOrFail();
    }

    /**
     * Contra account for a business: code 1199, auto-created if missing. Never delete it.
     */
    public function contraAccount(int $businessId): LedgerAccount
    {
        return LedgerAccount::firstOrCreate(
            ['business_id' => $businessId, 'code' => LedgerAccount::CONTRA_CODE],
            [
                'name'       => 'Asset Clearing Account',
                'type'       => 'asset',
                'group_name' => 'valuables',
                'is_active'  => true,
            ]
        );
    }

    /**
     * Look up an active account by its code within a business.
     */
    public function accountByCode(int $businessId, string $code): ?LedgerAccount
    {
        return LedgerAccount::query()
            ->where('business_id', $businessId)
            ->where('code', $code)
            ->first();
    }
}
