<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('name');
            $table->enum('type', ['asset', 'liability', 'income', 'cogs', 'expense']);
            $table->enum('group_name', ['valuables', 'debts', 'money_in', 'direct_costs', 'general_costs']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['business_id', 'code']);
            $table->index(['business_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_accounts');
    }
};
