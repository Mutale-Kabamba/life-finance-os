<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', [
                'land', 'building', 'vehicle', 'furniture', 'machinery',
                'equipment', 'electronics', 'livestock', 'other'
            ])->default('other');
            $table->decimal('purchase_price', 15, 2);
            $table->date('purchase_date');
            $table->decimal('current_value', 15, 2)->nullable();
            $table->decimal('depreciation_rate', 5, 2)->default(0);
            $table->string('location')->nullable();
            $table->string('serial_number')->nullable();
            $table->boolean('is_insured')->default(false);
            $table->string('insurance_provider')->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('asset_maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('cost', 15, 2);
            $table->date('maintenance_date');
            $table->string('provider')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenance');
        Schema::dropIfExists('assets');
    }
};
