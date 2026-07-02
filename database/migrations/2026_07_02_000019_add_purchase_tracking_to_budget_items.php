<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_items', function (Blueprint $table) {
            $table->boolean('is_purchased')->default(false)->after('actual_amount');
            $table->timestamp('purchased_at')->nullable()->after('is_purchased');
            $table->foreignId('account_id')->nullable()->after('purchased_at')
                ->constrained('accounts')->nullOnDelete();
            $table->foreignId('expense_id')->nullable()->after('account_id')
                ->constrained('expenses')->nullOnDelete();
            $table->foreignId('account_transaction_id')->nullable()->after('expense_id')
                ->constrained('account_transactions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('budget_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_transaction_id');
            $table->dropConstrainedForeignId('expense_id');
            $table->dropConstrainedForeignId('account_id');
            $table->dropColumn(['is_purchased', 'purchased_at']);
        });
    }
};
