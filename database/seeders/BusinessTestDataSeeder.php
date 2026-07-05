<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\LedgerAccount;
use App\Models\Profile;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BusinessTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $demoUser = User::firstOrCreate(
            ['email' => 'demo@lifefinanceos.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
            ]
        );

        Profile::updateOrCreate(
            ['user_id' => $demoUser->id],
            [
                'first_name' => 'Demo',
                'last_name' => 'User',
                'onboarding_completed' => true,
                'active_modules' => ['business'],
                'feature_registry' => [
                    'has_business' => true,
                ],
            ]
        );

        $business = Business::updateOrCreate(
            [
                'user_id' => $demoUser->id,
                'name' => 'Acme Retail Zambia',
            ],
            [
                'trading_name' => 'Acme Retail',
                'registration_number' => 'PACRA-2026-00123',
                'tax_number' => 'TPIN-1002003000',
                'type' => 'private_limited',
                'industry' => 'Retail & Distribution',
                'phone' => '+260971000111',
                'email' => 'accounts@acmeretail.co.zm',
                'address' => 'Plot 8, Cairo Road, Lusaka',
                'currency' => 'ZMW',
                'is_active' => true,
                'established_date' => '2023-01-15',
                'description' => 'Demo business seeded for testing business finance resources.',
            ]
        );

        $customerRows = [
            ['name' => 'Blue Valley Schools', 'email' => 'finance@bluevalley.sch.zm', 'phone' => '+260970111111'],
            ['name' => 'Lusaka Traders', 'email' => 'admin@lusakatraders.zm', 'phone' => '+260970222222'],
            ['name' => 'Green Farms Ltd', 'email' => 'payables@greenfarms.zm', 'phone' => '+260970333333'],
            ['name' => 'Ndeke Pharmacy', 'email' => 'owner@ndekepharmacy.zm', 'phone' => '+260970444444'],
            ['name' => 'Kafue Hardware', 'email' => 'accounts@kafuehardware.zm', 'phone' => '+260970555555'],
        ];

        foreach ($customerRows as $row) {
            Customer::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $row['name'],
                ],
                [
                    'email' => $row['email'],
                    'phone' => $row['phone'],
                    'address' => 'Lusaka, Zambia',
                    'tax_number' => null,
                    'credit_limit' => 50000,
                    'outstanding_balance' => 0,
                    'is_active' => true,
                    'notes' => 'Seeded customer for testing.',
                ]
            );
        }

        Customer::query()
            ->where('business_id', $business->id)
            ->whereNotIn('name', array_column($customerRows, 'name'))
            ->delete();

        $supplierRows = [
            ['name' => 'Zam Wholesale Distributors', 'email' => 'sales@zamwholesale.zm', 'phone' => '+260961111111'],
            ['name' => 'Copperbelt Packaging', 'email' => 'orders@cbpackaging.zm', 'phone' => '+260962222222'],
            ['name' => 'Prime Imports Ltd', 'email' => 'trade@primeimports.zm', 'phone' => '+260963333333'],
            ['name' => 'Quick Logistics Zambia', 'email' => 'ops@quicklogistics.zm', 'phone' => '+260964444444'],
            ['name' => 'Fresh Foods Suppliers', 'email' => 'hello@freshfoods.zm', 'phone' => '+260965555555'],
            ['name' => 'Zed Office Supplies', 'email' => 'sales@zedoffice.zm', 'phone' => '+260966666661'],
            ['name' => 'Manda Hill Beverage Hub', 'email' => 'orders@bevhub.zm', 'phone' => '+260966666662'],
            ['name' => 'Sunrise Agro Inputs', 'email' => 'trade@sunriseagro.zm', 'phone' => '+260966666663'],
            ['name' => 'Unity Cleaning Chemicals', 'email' => 'support@unityclean.zm', 'phone' => '+260966666664'],
            ['name' => 'Transit Freight Zambia', 'email' => 'ops@transitfreight.zm', 'phone' => '+260966666665'],
        ];

        foreach ($supplierRows as $row) {
            Supplier::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $row['name'],
                ],
                [
                    'email' => $row['email'],
                    'phone' => $row['phone'],
                    'address' => 'Lusaka, Zambia',
                    'tax_number' => null,
                    'outstanding_balance' => 0,
                    'is_active' => true,
                    'notes' => 'Seeded supplier for testing.',
                ]
            );
        }

        Supplier::query()
            ->where('business_id', $business->id)
            ->whereNotIn('name', array_column($supplierRows, 'name'))
            ->delete();

        $categories = [
            'Groceries' => 'Fast-moving household groceries.',
            'Beverages' => 'Soft drinks, juices, and water.',
            'Home Care' => 'Cleaning and sanitary products.',
            'Services' => 'Service-based items for billing and quotations.',
        ];

        $categoryIds = [];
        foreach ($categories as $name => $description) {
            $category = InventoryCategory::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $name,
                ],
                [
                    'description' => $description,
                ]
            );

            $categoryIds[$name] = $category->id;
        }

        $products = [];

        $productCategories = ['Groceries', 'Beverages', 'Home Care'];
        for ($i = 1; $i <= 40; $i++) {
            $category = $productCategories[($i - 1) % count($productCategories)];
            $baseCost = 12 + ($i * 2.75);

            $products[] = [
                'name' => sprintf('%s Item %02d', $category, $i),
                'sku' => sprintf('PRD-%04d', $i),
                'category' => $category,
                'cost' => round($baseCost, 2),
                'sell' => round($baseCost * 1.28, 2),
                'qty' => 40 + ($i * 3),
                'reorder' => 15 + ($i % 10),
                'unit' => 'each',
                'description' => sprintf('Seeded product %02d for testing.', $i),
            ];
        }

        $serviceNames = [
            'Delivery Service',
            'On-site Installation',
            'Equipment Maintenance',
            'Monthly Retainer',
            'Consulting Session',
            'Staff Training Package',
            'System Setup Service',
            'After-sales Support',
            'Product Customization',
            'Express Fulfilment',
        ];

        foreach ($serviceNames as $index => $serviceName) {
            $baseCost = 180 + ($index * 35);
            $products[] = [
                'name' => $serviceName,
                'sku' => sprintf('SRV-%04d', $index + 1),
                'category' => 'Services',
                'cost' => round($baseCost, 2),
                'sell' => round($baseCost * 1.4, 2),
                'qty' => 0,
                'reorder' => 0,
                'unit' => 'service',
                'description' => 'Seeded service item for testing.',
            ];
        }

        foreach ($products as $product) {
            Inventory::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'sku' => $product['sku'],
                ],
                [
                    'inventory_category_id' => $categoryIds[$product['category']] ?? null,
                    'name' => $product['name'],
                    'barcode' => null,
                    'cost_price' => $product['cost'],
                    'selling_price' => $product['sell'],
                    'quantity_on_hand' => $product['qty'],
                    'reorder_level' => $product['reorder'],
                    'unit' => $product['unit'],
                    'is_active' => true,
                    'description' => $product['description'],
                ]
            );
        }

        Inventory::query()
            ->where('business_id', $business->id)
            ->whereNotIn('sku', array_column($products, 'sku'))
            ->delete();

        $accounts = [
            ['code' => '1100', 'name' => 'Main Settlement Account', 'type' => 'asset', 'group_name' => 'valuables'],
            ['code' => '1199', 'name' => 'System Contra Account', 'type' => 'asset', 'group_name' => 'valuables'],
            ['code' => '1200', 'name' => 'Inventory Asset', 'type' => 'asset', 'group_name' => 'valuables'],
            ['code' => '1300', 'name' => 'Accounts Receivable', 'type' => 'asset', 'group_name' => 'valuables'],
            ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'liability', 'group_name' => 'debts'],
            ['code' => '2100', 'name' => 'Tax Payable', 'type' => 'liability', 'group_name' => 'debts'],
            ['code' => '4000', 'name' => 'Sales Revenue', 'type' => 'income', 'group_name' => 'money_in'],
            ['code' => '4100', 'name' => 'Other Operating Income', 'type' => 'income', 'group_name' => 'money_in'],
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => 'cogs', 'group_name' => 'direct_costs'],
            ['code' => '6000', 'name' => 'Utilities Expense', 'type' => 'expense', 'group_name' => 'general_costs'],
            ['code' => '6100', 'name' => 'Rent Expense', 'type' => 'expense', 'group_name' => 'general_costs'],
            ['code' => '6200', 'name' => 'Salaries Expense', 'type' => 'expense', 'group_name' => 'general_costs'],
        ];

        foreach ($accounts as $account) {
            LedgerAccount::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'code' => $account['code'],
                ],
                [
                    'name' => $account['name'],
                    'type' => $account['type'],
                    'group_name' => $account['group_name'],
                    'is_active' => true,
                ]
            );
        }
    }
}
