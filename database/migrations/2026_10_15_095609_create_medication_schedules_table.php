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
        Schema::create('medication_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_medication_id')->constrained()->cascadeOnDelete();

            $table->time('scheduled_time');
            $table->decimal('dose_amount', 10, 3);
            $table->string('dose_unit')->default('tablet');

            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->json('days_of_week')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_schedules');
    }
};
