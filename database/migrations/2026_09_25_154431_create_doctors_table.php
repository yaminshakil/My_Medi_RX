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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade'); // link with users table
            $table->string('phone')->nullable();
            $table->string('registration_no')->nullable();
            $table->string('gender')->nullable();
            $table->date('dob')->nullable();

            $table->string('working_institute')->nullable();
            $table->string('specialization')->nullable(); // e.g. Cardiology, Neurology
            $table->string('designation')->nullable();    // e.g. Assistant Professor, Consultant
            $table->string('qualification')->nullable();  // e.g. MBBS, FCPS
            $table->integer('experience_years')->nullable();

            $table->text('bio')->nullable();              // short introduction
            $table->json('social')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('featured')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
