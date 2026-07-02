<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->foreignId('account_id')->constrained('ledger_accounts')->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('parent_transaction_id')->nullable()->constrained('ledger_transactions')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('ledger_categories')->nullOnDelete();
            $table->string('description')->nullable();
            $table->string('payment_status')->default('pending');
            $table->boolean('is_reconciled')->default(false);
            $table->dateTime('reconciled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'date']);
            $table->index(['account_id', 'is_reconciled']);
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_transactions');
    }
};
