<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('doctor_rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('doctor_shifts')->nullOnDelete();
            $table->date('roster_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room_label', 100)->nullable();
            $table->string('status', 20)->default('scheduled'); // scheduled, completed, cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['hospital_id', 'roster_date']);
            $table->index(['doctor_id', 'roster_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_rosters');
    }
};
