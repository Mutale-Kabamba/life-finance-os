<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Dynamic feature flags toggled by the onboarding wizard,
            // e.g. {"has_business": true, "has_spouse": false, "has_children": true}.
            $table->json('feature_registry')->nullable()->after('active_modules');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('feature_registry');
        });
    }
};
