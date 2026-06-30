<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('income_receipts', function (Blueprint $table) {
            $table->foreignId('receivable_payment_id')
                ->nullable()
                ->after('income_source_id')
                ->constrained('receivable_payments')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('income_receipts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('receivable_payment_id');
        });
    }
};
