<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'google_access_token')) {
                $table->text('google_access_token')->nullable()->after('github_id');
            }

            if (! Schema::hasColumn('users', 'google_refresh_token')) {
                $table->text('google_refresh_token')->nullable()->after('google_access_token');
            }

            if (! Schema::hasColumn('users', 'google_token_expires_at')) {
                $table->timestamp('google_token_expires_at')->nullable()->after('google_refresh_token');
            }

            if (! Schema::hasColumn('users', 'google_calendar_id')) {
                $table->string('google_calendar_id')->nullable()->after('google_token_expires_at');
            }

            if (! Schema::hasColumn('users', 'google_calendar_sync_token')) {
                $table->text('google_calendar_sync_token')->nullable()->after('google_calendar_id');
            }

            if (! Schema::hasColumn('users', 'google_calendar_connected_at')) {
                $table->timestamp('google_calendar_connected_at')->nullable()->after('google_calendar_sync_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $dropColumns = [];

            foreach ([
                'google_access_token',
                'google_refresh_token',
                'google_token_expires_at',
                'google_calendar_id',
                'google_calendar_sync_token',
                'google_calendar_connected_at',
            ] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if (! empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
