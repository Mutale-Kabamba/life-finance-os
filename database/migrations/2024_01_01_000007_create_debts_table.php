<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('creditor_name');
            $table->enum('type', [
                'bank_loan', 'mobile_loan', 'mortgage', 'vehicle_loan',
                'personal_loan', 'hire_purchase', 'credit_card', 'student_loan', 'other'
            ])->default('personal_loan');
            $table->decimal('original_amount', 15, 2);
            $table->decimal('outstanding_balance', 15, 2);
            $table->decimal('monthly_installment', 15, 2);
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('status', ['active', 'paid_off', 'defaulted', 'restructured'])->default('active');
            $table->string('account_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('debt_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->decimal('principal_paid', 15, 2)->default(0);
            $table->decimal('interest_paid', 15, 2)->default(0);
            $table->date('payment_date');
            $table->boolean('is_late')->default(false);
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_payments');
        Schema::dropIfExists('debts');
    }
};
