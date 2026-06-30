<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference')->unique();
            $table->enum('type', ['income', 'expense', 'transfer', 'saving', 'debt_payment', 'investment'])->default('expense');
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->string('payment_method')->nullable();
            $table->string('transactable_type')->nullable();
            $table->unsignedBigInteger('transactable_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['transactable_type', 'transactable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
