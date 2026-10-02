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
        Schema::create('instructions', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->boolean('is_global')->default(false); // global or not
            $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('cascade'); // null for global
            $table->timestamps();
        });

        Schema::create('doctor_instruction_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('instruction_id')->constrained('instructions')->onDelete('cascade');
            $table->integer('sort_order')->default(0); // doctor's custom order
            $table->unique(['doctor_id', 'instruction_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructions');
        Schema::dropIfExists('doctor_instruction_orders');
    }
};
