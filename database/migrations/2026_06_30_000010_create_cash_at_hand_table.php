<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_at_hand', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->date('date')->index();
            $table->enum('type', ['opening_balance', 'deposit', 'withdrawal', 'bank_deposit'])->index();
            $table->decimal('amount', 15, 2);
            $table->string('description')->nullable();
            $table->string('reference')->nullable()->unique();
            $table->foreignId('ledger_transaction_id')->nullable()->constrained('ledger_transactions')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->boolean('is_reconciled')->default(false);
            $table->dateTime('reconciled_at')->nullable();
            $table->string('reconciliation_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['date', 'type']);
            $table->index(['business_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_at_hand');
    }
};
