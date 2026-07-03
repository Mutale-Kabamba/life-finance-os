<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_at_hand_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->date('reconciliation_date');
            $table->decimal('opening_balance', 15, 2);
            $table->decimal('total_deposits', 15, 2)->default(0);
            $table->decimal('total_withdrawals', 15, 2)->default(0);
            $table->decimal('expected_balance', 15, 2);
            $table->decimal('actual_balance', 15, 2)->nullable();
            $table->decimal('variance', 15, 2)->nullable();
            $table->enum('status', ['pending', 'reconciled', 'variance'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->unique(['business_id', 'reconciliation_date'], 'cah_recon_business_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_at_hand_reconciliations');
    }
};
