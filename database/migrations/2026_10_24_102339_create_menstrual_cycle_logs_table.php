<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menstrual_cycle_logs', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('menstrual_cycle_profile_id')
                ->constrained('menstrual_cycle_profiles')
                ->cascadeOnDelete();

            $table->date('entry_date');

            $table->string('flow', 20)->default('none');
            $table->string('cramps', 20)->default('none');
            $table->string('mood', 40)->nullable();

            $table->unsignedTinyInteger('water_glasses')->default(0);

            $table->json('symptoms')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(
                ['menstrual_cycle_profile_id', 'entry_date'],
                'cycle_profile_entry_date_unique'
            );

            $table->index('entry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menstrual_cycle_logs');
    }
};
