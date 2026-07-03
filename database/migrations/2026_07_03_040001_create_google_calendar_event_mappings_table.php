<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_calendar_event_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('financial_calendar_id')->nullable()->constrained('financial_calendar')->nullOnDelete();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('google_calendar_id');
            $table->string('google_event_id');
            $table->string('google_event_etag')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->enum('sync_origin', ['local', 'google'])->default('local');
            $table->enum('sync_status', ['active', 'deleted', 'conflict'])->default('active');
            $table->timestamps();

            $table->unique(['user_id', 'google_calendar_id', 'google_event_id'], 'gcal_unique_remote_event');
            $table->unique(['user_id', 'source_type', 'source_id'], 'gcal_unique_source_event');
            $table->index(['user_id', 'sync_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_calendar_event_mappings');
    }
};
