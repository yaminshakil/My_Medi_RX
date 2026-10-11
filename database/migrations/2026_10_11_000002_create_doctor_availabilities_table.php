<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('doctor_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('doctor_shifts')->nullOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0 Sunday ... 6 Saturday
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('slot_duration')->default(15);
            $table->unsignedSmallInteger('max_patients')->nullable();
            $table->string('room_label', 100)->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->index(['hospital_id', 'doctor_id', 'day_of_week']);
            $table->index(['department_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_availabilities');
    }
};
