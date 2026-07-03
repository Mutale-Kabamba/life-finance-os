<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('debts', function (Blueprint $table): void {
            if (! Schema::hasColumn('debts', 'total_repayment_amount')) {
                $table->decimal('total_repayment_amount', 15, 2)->nullable()->after('interest_rate');
            }

            if (! Schema::hasColumn('debts', 'repayment_frequency')) {
                $table->enum('repayment_frequency', ['daily', 'weekly', 'bi_weekly', 'monthly'])
                    ->nullable()
                    ->after('total_repayment_amount');
            }
        });

        Schema::table('expenses', function (Blueprint $table): void {
            if (! Schema::hasColumn('expenses', 'is_mandatory')) {
                $table->boolean('is_mandatory')->default(false)->after('is_recurring');
            }
        });

        Schema::table('income_receipts', function (Blueprint $table): void {
            if (! Schema::hasColumn('income_receipts', 'account_id')) {
                $table->foreignId('account_id')
                    ->nullable()
                    ->after('income_source_id')
                    ->constrained('accounts')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('income_receipts', 'account_transaction_id')) {
                $table->foreignId('account_transaction_id')
                    ->nullable()
                    ->after('account_id')
                    ->constrained('account_transactions')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('income_receipts', function (Blueprint $table): void {
            if (Schema::hasColumn('income_receipts', 'account_transaction_id')) {
                $table->dropConstrainedForeignId('account_transaction_id');
            }

            if (Schema::hasColumn('income_receipts', 'account_id')) {
                $table->dropConstrainedForeignId('account_id');
            }
        });

        Schema::table('expenses', function (Blueprint $table): void {
            if (Schema::hasColumn('expenses', 'is_mandatory')) {
                $table->dropColumn('is_mandatory');
            }
        });

        Schema::table('debts', function (Blueprint $table): void {
            if (Schema::hasColumn('debts', 'repayment_frequency')) {
                $table->dropColumn('repayment_frequency');
            }

            if (Schema::hasColumn('debts', 'total_repayment_amount')) {
                $table->dropColumn('total_repayment_amount');
            }
        });
    }
};
