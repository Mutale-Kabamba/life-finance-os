<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\ExpenseCategory;
use Illuminate\Support\Str;

class ExpenseCategoryDefaults
{
    /**
     * @return array<int, array{name: string, slug: string, icon: string, color: string, is_system: bool}>
     */
    public static function defaults(): array
    {
        return [
            ['name' => 'Housing & Rent', 'slug' => 'housing', 'icon' => 'heroicon-o-home', 'color' => '#3B82F6', 'is_system' => true],
            ['name' => 'Utilities', 'slug' => 'utilities', 'icon' => 'heroicon-o-bolt', 'color' => '#EAB308', 'is_system' => true],
            ['name' => 'Food & Groceries', 'slug' => 'food', 'icon' => 'heroicon-o-shopping-cart', 'color' => '#22C55E', 'is_system' => true],
            ['name' => 'Transport', 'slug' => 'transport', 'icon' => 'heroicon-o-truck', 'color' => '#8B5CF6', 'is_system' => true],
            ['name' => 'Communication', 'slug' => 'communication', 'icon' => 'heroicon-o-device-phone-mobile', 'color' => '#06B6D4', 'is_system' => true],
            ['name' => 'Health & Medical', 'slug' => 'health', 'icon' => 'heroicon-o-heart', 'color' => '#EF4444', 'is_system' => true],
            ['name' => 'Education', 'slug' => 'education', 'icon' => 'heroicon-o-academic-cap', 'color' => '#F97316', 'is_system' => true],
            ['name' => 'Entertainment', 'slug' => 'entertainment', 'icon' => 'heroicon-o-film', 'color' => '#EC4899', 'is_system' => true],
            ['name' => 'Giving & Donations', 'slug' => 'giving', 'icon' => 'heroicon-o-gift', 'color' => '#14B8A6', 'is_system' => true],
            ['name' => 'Family Support', 'slug' => 'family', 'icon' => 'heroicon-o-user-group', 'color' => '#0EA5E9', 'is_system' => true],
            ['name' => 'Emergency', 'slug' => 'emergency', 'icon' => 'heroicon-o-exclamation-triangle', 'color' => '#DC2626', 'is_system' => true],
            ['name' => 'Clothing & Personal', 'slug' => 'clothing', 'icon' => 'heroicon-o-shopping-bag', 'color' => '#A855F7', 'is_system' => true],
            ['name' => 'Insurance', 'slug' => 'insurance', 'icon' => 'heroicon-o-shield-check', 'color' => '#64748B', 'is_system' => true],
            ['name' => 'Loan Repayments', 'slug' => 'loans', 'icon' => 'heroicon-o-credit-card', 'color' => '#B91C1C', 'is_system' => true],
            ['name' => 'Business Expenses', 'slug' => 'business', 'icon' => 'heroicon-o-building-office', 'color' => '#1D4ED8', 'is_system' => true],
            ['name' => 'Travel', 'slug' => 'travel', 'icon' => 'heroicon-o-globe-alt', 'color' => '#0891B2', 'is_system' => true],
            ['name' => 'Personal Development', 'slug' => 'development', 'icon' => 'heroicon-o-light-bulb', 'color' => '#D97706', 'is_system' => true],
            ['name' => 'Other', 'slug' => 'other', 'icon' => 'heroicon-o-ellipsis-horizontal', 'color' => '#6B7280', 'is_system' => true],
        ];
    }

    public static function ensure(): void
    {
        if (ExpenseCategory::query()->exists()) {
            return;
        }

        foreach (self::defaults() as $category) {
            ExpenseCategory::query()->firstOrCreate(
                ['slug' => $category['slug']],
                $category,
            );
        }
    }

    /**
     * @return array<int, string>
     */
    public static function options(): array
    {
        self::ensure();

        return ExpenseCategory::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public static function defaultCategoryId(): int
    {
        self::ensure();

        $other = ExpenseCategory::query()->where('slug', 'other')->value('id');

        return (int) ($other ?: ExpenseCategory::query()->value('id'));
    }

    public static function createFromName(string $name): int
    {
        $clean = trim($name);

        if ($clean === '') {
            return self::defaultCategoryId();
        }

        $baseSlug = Str::slug($clean);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'category';
        $slug = $baseSlug;
        $counter = 2;

        while (ExpenseCategory::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return (int) ExpenseCategory::query()->create([
            'name' => Str::title($clean),
            'slug' => $slug,
            'is_system' => false,
        ])->getKey();
    }
}
