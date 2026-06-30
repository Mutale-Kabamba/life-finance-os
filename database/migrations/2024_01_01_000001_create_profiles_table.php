<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('national_id', 50)->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('address')->nullable();
            $table->string('avatar')->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->default('single');
            $table->enum('employment_status', ['employed', 'self_employed', 'unemployed', 'student', 'retired'])->default('employed');
            $table->enum('housing_type', ['own', 'renting', 'family', 'company'])->default('renting');
            $table->decimal('monthly_housing_cost', 15, 2)->default(0);
            $table->boolean('onboarding_completed')->default(false);
            $table->json('active_modules')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
