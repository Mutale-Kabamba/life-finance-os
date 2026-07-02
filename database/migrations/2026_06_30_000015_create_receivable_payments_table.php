<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receivable_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receivable_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('method', 50)->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['receivable_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receivable_payments');
    }
};
