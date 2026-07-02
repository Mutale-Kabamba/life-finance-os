<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\Business;
use App\Models\LedgerAccount;

class ChartOfAccountsService
{
    /**
     * Default chart of accounts template. Codes 1100 (settlement) and 1199 (contra)
     * are special and must remain. Rename names per business domain as needed.
     *
     * @var list<array{code:string,name:string,type:string,group_name:string}>
     */
    public const TEMPLATE = [
        ['code' => '1100', 'name' => 'Cash / Bank', 'type' => 'asset', 'group_name' => 'valuables'],
        ['code' => '1199', 'name' => 'Asset Clearing Account', 'type' => 'asset', 'group_name' => 'valuables'],
        ['code' => '1200', 'name' => 'Inventory', 'type' => 'asset', 'group_name' => 'valuables'],
        ['code' => '1500', 'name' => 'Equipment', 'type' => 'asset', 'group_name' => 'valuables'],
        ['code' => '1600', 'name' => 'Vehicles', 'type' => 'asset', 'group_name' => 'valuables'],
        ['code' => '2100', 'name' => 'Supplier Bills (Accounts Payable)', 'type' => 'liability', 'group_name' => 'debts'],
        ['code' => '2200', 'name' => 'Statutory Payables (ZRA/NAPSA)', 'type' => 'liability', 'group_name' => 'debts'],
        ['code' => '4100', 'name' => 'Sales Revenue', 'type' => 'income', 'group_name' => 'money_in'],
        ['code' => '4200', 'name' => 'Service Fees', 'type' => 'income', 'group_name' => 'money_in'],
        ['code' => '5100', 'name' => 'Cost of Goods Sold', 'type' => 'cogs', 'group_name' => 'direct_costs'],
        ['code' => '5200', 'name' => 'Direct Wages', 'type' => 'cogs', 'group_name' => 'direct_costs'],
        ['code' => '5300', 'name' => 'Power / Fuel', 'type' => 'cogs', 'group_name' => 'direct_costs'],
        ['code' => '6100', 'name' => 'Admin Salaries', 'type' => 'expense', 'group_name' => 'general_costs'],
        ['code' => '6200', 'name' => 'Marketing', 'type' => 'expense', 'group_name' => 'general_costs'],
        ['code' => '6300', 'name' => 'Repairs & Maintenance', 'type' => 'expense', 'group_name' => 'general_costs'],
        ['code' => '6400', 'name' => 'Depreciation', 'type' => 'expense', 'group_name' => 'general_costs'],
    ];

    /**
     * Seed (idempotently) the default chart of accounts for a business.
     */
    public function seedDefaults(Business $business): void
    {
        foreach (self::TEMPLATE as $account) {
            LedgerAccount::updateOrCreate(
                ['business_id' => $business->id, 'code' => $account['code']],
                [
                    'name'       => $account['name'],
                    'type'       => $account['type'],
                    'group_name' => $account['group_name'],
                    'is_active'  => true,
                ]
            );
        }
    }
}
