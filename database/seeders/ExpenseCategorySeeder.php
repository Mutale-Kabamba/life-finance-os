<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Housing & Rent',       'slug' => 'housing',       'icon' => 'heroicon-o-home',            'color' => '#3B82F6'],
            ['name' => 'Utilities',             'slug' => 'utilities',     'icon' => 'heroicon-o-bolt',            'color' => '#EAB308'],
            ['name' => 'Food & Groceries',      'slug' => 'food',          'icon' => 'heroicon-o-shopping-cart',   'color' => '#22C55E'],
            ['name' => 'Transport',             'slug' => 'transport',     'icon' => 'heroicon-o-truck',           'color' => '#8B5CF6'],
            ['name' => 'Communication',         'slug' => 'communication', 'icon' => 'heroicon-o-device-phone-mobile', 'color' => '#06B6D4'],
            ['name' => 'Health & Medical',      'slug' => 'health',        'icon' => 'heroicon-o-heart',           'color' => '#EF4444'],
            ['name' => 'Education',             'slug' => 'education',     'icon' => 'heroicon-o-academic-cap',    'color' => '#F97316'],
            ['name' => 'Entertainment',         'slug' => 'entertainment', 'icon' => 'heroicon-o-film',            'color' => '#EC4899'],
            ['name' => 'Giving & Donations',    'slug' => 'giving',        'icon' => 'heroicon-o-gift',            'color' => '#14B8A6'],
            ['name' => 'Family Support',        'slug' => 'family',        'icon' => 'heroicon-o-user-group',      'color' => '#0EA5E9'],
            ['name' => 'Emergency',             'slug' => 'emergency',     'icon' => 'heroicon-o-exclamation-triangle', 'color' => '#DC2626'],
            ['name' => 'Clothing & Personal',   'slug' => 'clothing',      'icon' => 'heroicon-o-shopping-bag',    'color' => '#A855F7'],
            ['name' => 'Insurance',             'slug' => 'insurance',     'icon' => 'heroicon-o-shield-check',    'color' => '#64748B'],
            ['name' => 'Loan Repayments',       'slug' => 'loans',         'icon' => 'heroicon-o-credit-card',     'color' => '#B91C1C'],
            ['name' => 'Business Expenses',     'slug' => 'business',      'icon' => 'heroicon-o-building-office', 'color' => '#1D4ED8'],
            ['name' => 'Travel',                'slug' => 'travel',        'icon' => 'heroicon-o-globe-alt',       'color' => '#0891B2'],
            ['name' => 'Personal Development',  'slug' => 'development',   'icon' => 'heroicon-o-light-bulb',      'color' => '#D97706'],
            ['name' => 'Other',                 'slug' => 'other',         'icon' => 'heroicon-o-ellipsis-horizontal', 'color' => '#6B7280'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::firstOrCreate(
                ['slug' => $category['slug']],
                array_merge($category, ['is_system' => true])
            );
        }
    }
}
