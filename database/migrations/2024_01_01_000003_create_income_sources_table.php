<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', [
                'salary', 'business', 'freelancing', 'farming',
                'rental', 'investment', 'side_hustle', 'pension', 'other'
            ])->default('other');
            $table->decimal('amount', 15, 2);
            $table->enum('frequency', ['daily', 'weekly', 'bi_weekly', 'monthly', 'quarterly', 'annually'])->default('monthly');
            $table->date('start_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_sources');
    }
};
