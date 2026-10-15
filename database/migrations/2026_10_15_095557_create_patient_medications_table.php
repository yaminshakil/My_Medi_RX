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
        Schema::create('patient_medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')
        ->constrained()
        ->cascadeOnDelete();

            $table->foreignId('prescription_medicine_id')
                ->nullable()
                ->constrained('prescription_medicines')
                ->nullOnDelete();

            $table->string('medicine_name');
            $table->string('strength')->nullable();
            $table->string('dosage')->nullable();
            $table->string('dosage_unit')->nullable();

            $table->enum('source', [
                'doctor_prescription',
                'qr_scan',
                'ocr_scan',
                'manual',
            ])->default('manual');

            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->unsignedInteger('total_quantity')->nullable();
            $table->unsignedInteger('remaining_quantity')->nullable();
            $table->unsignedInteger('refill_threshold')->default(5);

            $table->text('instructions')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_medications');
    }
};
