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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_number')->unique();

            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('chamber_id')->constrained('doctor_chambers')->onDelete('cascade');
            // Appointment details
            $table->date('appointment_date');
            $table->time('appointment_time')->nullable(); // optional if only date needed
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'absent'])
                ->default('pending');

            $table->string('consultation_type')->default('offline'); // offline/online
            $table->enum('appointment_type', ['offline', 'online'])
                ->default('offline');
            $table->text('notes')->nullable();

            // Payment info (optional)
            $table->decimal('fee', 10, 2)->nullable();
            $table->boolean('is_paid')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
