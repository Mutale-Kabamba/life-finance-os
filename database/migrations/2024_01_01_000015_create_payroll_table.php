<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('position');
            $table->string('department')->nullable();
            $table->decimal('gross_salary', 15, 2);
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            $table->enum('status', ['active', 'terminated', 'on_leave'])->default('active');
            $table->string('national_id', 50)->nullable();
            $table->string('tpin', 20)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('pay_period_start');
            $table->date('pay_period_end');
            $table->date('payment_date');
            $table->decimal('total_gross', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('total_net', 15, 2)->default(0);
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->timestamps();
        });

        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('gross_salary', 15, 2);
            $table->decimal('napsa_employee', 15, 2)->default(0);
            $table->decimal('napsa_employer', 15, 2)->default(0);
            $table->decimal('nhima_employee', 15, 2)->default(0);
            $table->decimal('paye', 15, 2)->default(0);
            $table->decimal('other_deductions', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('employees');
    }
};
