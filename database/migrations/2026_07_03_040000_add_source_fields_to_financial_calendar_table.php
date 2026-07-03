<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_calendar', function (Blueprint $table): void {
            if (! Schema::hasColumn('financial_calendar', 'source_type')) {
                $table->string('source_type')->nullable()->after('type');
            }

            if (! Schema::hasColumn('financial_calendar', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            }

            if (! Schema::hasColumn('financial_calendar', 'source_label')) {
                $table->string('source_label')->nullable()->after('source_id');
            }

            $table->index(['user_id', 'source_type', 'source_id'], 'financial_calendar_source_idx');
        });
    }

    public function down(): void
    {
        Schema::table('financial_calendar', function (Blueprint $table): void {
            if (Schema::hasColumn('financial_calendar', 'source_label')) {
                $table->dropColumn('source_label');
            }

            if (Schema::hasColumn('financial_calendar', 'source_id')) {
                $table->dropColumn('source_id');
            }

            if (Schema::hasColumn('financial_calendar', 'source_type')) {
                $table->dropColumn('source_type');
            }

            $table->dropIndex('financial_calendar_source_idx');
        });
    }
};
