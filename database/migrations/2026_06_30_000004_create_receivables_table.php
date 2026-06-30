<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receivables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('debtor_name');
            $table->string('phone', 20)->nullable();
            $table->decimal('amount', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'partially_paid', 'paid', 'written_off'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });

        // Receivables now live in their own table — drop the temporary JSON column.
        if (Schema::hasColumn('profiles', 'receivables')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->dropColumn('receivables');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('receivables');

        if (! Schema::hasColumn('profiles', 'receivables')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->json('receivables')->nullable()->after('feature_registry');
            });
        }
    }
};
