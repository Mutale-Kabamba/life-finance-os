<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('family_name');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('spouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('national_id', 50)->nullable();
            $table->enum('employment_status', ['employed', 'self_employed', 'unemployed', 'student', 'homemaker'])->default('employed');
            $table->decimal('monthly_income', 15, 2)->default(0);
            $table->date('marriage_date')->nullable();
            $table->timestamps();
        });

        Schema::create('children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->string('school_name')->nullable();
            $table->string('grade')->nullable();
            $table->decimal('annual_school_fees', 15, 2)->default(0);
            $table->decimal('monthly_transport', 15, 2)->default(0);
            $table->decimal('monthly_medical', 15, 2)->default(0);
            $table->decimal('monthly_other', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('children');
        Schema::dropIfExists('spouses');
        Schema::dropIfExists('families');
    }
};
