<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('target_amount', 15, 2)->nullable();
            $table->date('target_date')->nullable();
            $table->enum('status', ['not_started', 'in_progress', 'achieved', 'cancelled'])->default('not_started');
            $table->integer('priority')->default(1);
            $table->timestamps();
        });

        Schema::create('financial_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['tip', 'warning', 'alert', 'congratulation', 'reminder'])->default('tip');
            $table->string('icon')->nullable();
            $table->boolean('is_read')->default(false);
            $table->date('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('financial_calendar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', [
                'bill', 'school_fees', 'loan_payment', 'insurance',
                'savings_target', 'salary', 'investment_review', 'tax_deadline',
                'customer_payment', 'supplier_payment', 'payroll', 'other'
            ])->default('other');
            $table->date('due_date');
            $table->decimal('amount', 15, 2)->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_pattern')->nullable();
            $table->boolean('notify_before')->default(true);
            $table->integer('notify_days_before')->default(3);
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_calendar');
        Schema::dropIfExists('financial_insights');
        Schema::dropIfExists('financial_goals');
    }
};
