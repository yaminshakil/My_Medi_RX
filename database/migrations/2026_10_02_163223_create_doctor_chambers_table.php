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
        Schema::create('doctor_chambers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->nullable()->constrained('hospitals')->onDelete('cascade');
            $table->longText('header_left');
            $table->longText('header_right');
            $table->longText('footer_info');
            $table->json('schedules');
            $table->decimal('fee', total: 8, places: 2);
            $table->decimal('followup_fee', total: 8, places: 2);
            $table->decimal('report_fee', total: 8, places: 2)->nullable();
            $table->string('chamber_logo')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('is_active')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_chambers');
    }
};
