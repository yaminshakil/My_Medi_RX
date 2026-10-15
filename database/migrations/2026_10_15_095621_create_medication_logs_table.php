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
        Schema::create('medication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')
        ->constrained()
        ->cascadeOnDelete();

            $table->foreignId('patient_medication_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('medication_schedule_id')
                ->nullable()
                ->constrained('medication_schedules')
                ->nullOnDelete();

            $table->date('scheduled_date');
            $table->dateTime('scheduled_at');
            $table->dateTime('taken_at')->nullable();

            $table->string('status')->default('upcoming');
            // upcoming, taken, missed, skipped, snoozed

            $table->dateTime('snoozed_until')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique([
                'medication_schedule_id',
                'scheduled_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_logs');
    }
};
