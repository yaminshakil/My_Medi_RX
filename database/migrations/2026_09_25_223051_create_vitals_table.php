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
        Schema::create('vitals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade'); // Link to patients table

            // Vital signs
            $table->string('blood_pressure')->nullable(); // e.g., "120/80"
            $table->integer('heart_rate')->nullable(); // bpm
            $table->decimal('temperature', 4, 1)->nullable(); // Celsius, e.g., 37.5
            $table->integer('respiratory_rate')->nullable(); // breaths per minute
            $table->integer('oxygen_saturation')->nullable(); // percentage
            $table->decimal('weight', 5, 2)->nullable(); // kg
            $table->decimal('height', 5, 2)->nullable(); // cm
            $table->decimal('bmi', 5, 2)->nullable(); // body mass index

            // Optional notes
            $table->text('notes')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vitals');
    }
};
