<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // People who owe the user money, captured during onboarding.
            // e.g. [{"name": "...", "amount": 100, "due_date": "2026-01-01", "notes": "..."}]
            $table->json('receivables')->nullable()->after('feature_registry');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('receivables');
        });
    }
};
