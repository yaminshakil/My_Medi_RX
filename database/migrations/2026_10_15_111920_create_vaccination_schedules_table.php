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
        Schema::create('vaccination_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vaccine_id')
                ->constrained('vaccines')
                ->cascadeOnDelete();

            $table->string('schedule_name')->nullable();
            $table->unsignedSmallInteger('dose_number');

            // Recommended age from birth, expressed in days.
            $table->unsignedInteger('recommended_age_days')->nullable();

            // Optional interval from the previous dose, in days.
            $table->unsignedInteger('interval_days')->nullable();

            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(
                ['vaccine_id', 'schedule_name', 'dose_number'],
                'vaccination_schedules_vaccine_name_dose_unique'
            );

            $table->index(['vaccine_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaccination_schedules');
    }
};
