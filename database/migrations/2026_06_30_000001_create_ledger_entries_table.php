<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            // UUID primary key.
            $table->uuid('id')->primary();

            // Owning user.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Polymorphic financial context (e.g. PersonalAccount, Company, Business...).
            // Creates: financial_context_id (unsignedBigInteger) + financial_context_type (string)
            // and a composite index on (type, id).
            $table->morphs('financial_context');

            // Double-entry accounting fields.
            $table->string('chart_of_accounts_code');
            $table->enum('entry_type', ['debit', 'credit']);
            $table->decimal('amount', 15, 4);
            $table->string('currency_code', 3)->default('ZMW');
            $table->timestamp('posted_at')->index();

            $table->timestamps();

            // Performance indexes for common lookups.
            $table->index(['user_id', 'posted_at']);
            $table->index('chart_of_accounts_code');
            $table->index(['financial_context_type', 'financial_context_id', 'entry_type'], 'ledger_context_entry_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
