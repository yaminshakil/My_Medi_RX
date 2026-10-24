<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pediatric_dose_calculations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('medicine_id')
                ->constrained('medicines')
                ->cascadeOnDelete();

            $table->foreignId('pediatric_dose_rule_id')
                ->nullable()
                ->constrained('pediatric_dose_rules')
                ->nullOnDelete();

            // Input snapshot
            $table->decimal('child_weight_kg', 7, 3);
            $table->unsignedInteger('child_age_months');
            $table->string('indication');
            $table->string('formulation');

            // Result and rule snapshot for auditability
            $table->decimal('calculated_dose_mg', 10, 3)->nullable();
            $table->decimal('calculated_volume_ml', 10, 3)->nullable();
            $table->decimal('concentration_mg', 10, 3)->nullable();
            $table->decimal('concentration_ml', 10, 3)->nullable();
            $table->decimal('rule_dose_mg_per_kg', 10, 4)->nullable();
            $table->decimal('max_single_dose_mg', 10, 3)->nullable();
            $table->string('route')->nullable();
            $table->unsignedInteger('dose_interval_hours')->nullable();
            $table->unsignedInteger('max_doses_per_24_hours')->nullable();
            $table->string('source_reference')->nullable();

            $table->string('status')->default('calculated');
            $table->json('safety_checks')->nullable();

            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'calculated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pediatric_dose_calculations');
    }
};
