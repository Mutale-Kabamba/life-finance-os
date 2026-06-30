<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', [
                'stocks', 'bonds', 'treasury_bills', 'fixed_deposit',
                'unit_trust', 'mutual_fund', 'real_estate', 'cryptocurrency',
                'business', 'farming', 'other'
            ])->default('other');
            $table->string('institution')->nullable();
            $table->decimal('initial_amount', 15, 2);
            $table->decimal('current_value', 15, 2)->default(0);
            $table->decimal('expected_return_rate', 5, 2)->default(0);
            $table->date('start_date');
            $table->date('maturity_date')->nullable();
            $table->enum('status', ['active', 'matured', 'sold', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
