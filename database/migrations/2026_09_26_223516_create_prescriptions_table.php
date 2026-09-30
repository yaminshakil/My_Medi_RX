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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('prescription_number')->unique();

            // Relations
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('set null');
            // assuming doctors are stored in users table

            // Prescription details
            $table->date('followup_date')->default(now());
            $table->boolean('is_followup')->default(false);
            $table->string('diagnosis')->nullable();
            $table->json('symptoms')->nullable();
            $table->json('onexaminations')->nullable();
            $table->json('investigations')->nullable();

            // Optional vitals snapshot
            $table->foreignId('vital_id')->nullable()->constrained('vitals')->onDelete('set null');

            // Notes / instructions
            $table->text('instructions')->nullable();
            $table->text('follow_up_advice')->nullable();
            // Chamber I
            $table->string('chamber_logo')->nullable();
            $table->longText('header_left');
            $table->longText('header_right');
            $table->longText('footer_info');
            // Status
            $table->enum('status', ['draft', 'issued', 'completed', 'cancelled'])->default('issued');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
