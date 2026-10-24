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
        Schema::create('vaccination_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('patient_id')
                ->constrained('patients')
                ->cascadeOnDelete();

            $table->foreignId('vaccine_id')
                ->constrained('vaccines')
                ->restrictOnDelete();

            $table->foreignId('vaccination_schedule_id')
                ->nullable()
                ->constrained('vaccination_schedules')
                ->nullOnDelete();

            $table->unsignedSmallInteger('dose_number')->nullable();

            $table->date('scheduled_date')->nullable();
            $table->dateTime('administered_at')->nullable();

            $table->string('status')->default('scheduled');
            // Expected values: scheduled, completed, missed, cancelled.

            $table->string('provider_name')->nullable();
            $table->string('facility_name')->nullable();
            $table->string('batch_number')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['patient_id', 'scheduled_date']);
            $table->index(['patient_id', 'status']);
            $table->index(['vaccine_id', 'administered_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaccination_records');
    }
};
