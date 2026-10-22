<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pediatric_dose_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')
                ->constrained('medicines')
                ->cascadeOnDelete();

            $table->string('formulation');
            $table->string('indication');

            // Concentration, e.g. mg contained in a stated mL volume.
            $table->decimal('concentration_mg', 10, 3);
            $table->decimal('concentration_ml', 10, 3);

            // Approved amount per dose, not per day.
            $table->decimal('dose_mg_per_kg_per_dose', 10, 4);
            $table->decimal('max_single_dose_mg', 10, 3)->nullable();

            // Limits must be reviewed and supplied by an authorized clinician.
            $table->decimal('max_daily_mg_per_kg', 10, 4)->nullable();
            $table->decimal('max_daily_dose_mg', 10, 3)->nullable();

            $table->unsignedInteger('dose_interval_hours')->nullable();
            $table->unsignedInteger('max_doses_per_24_hours')->nullable();

            $table->unsignedInteger('min_age_months')->default(0);
            $table->unsignedInteger('max_age_months')->nullable();

            $table->decimal('min_weight_kg', 7, 3)->nullable();
            $table->decimal('max_weight_kg', 7, 3)->nullable();

            $table->string('route')->default('oral');
            $table->text('contraindications')->nullable();
            $table->text('notes')->nullable();

            $table->string('source_reference');
            $table->string('source_version')->nullable();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();
            $table->boolean('is_active')->default(false);

            $table->timestamps();

            $table->index([
                'medicine_id',
                'indication',
                'is_active',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pediatric_dose_rules');
    }
};
