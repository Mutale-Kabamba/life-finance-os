<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('income_source_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->decimal('amount', 15, 2);
            $table->date('received_date');
            $table->string('method', 50)->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'received_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_receipts');
    }
};
