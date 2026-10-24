<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pregnancy_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->date('last_menstrual_period')->nullable();
            $table->date('due_date')->nullable();
            $table->date('dating_confirmed_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pregnancy_weekly_highlights', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('week')->unique();
            $table->string('fruit_name', 100);
            $table->decimal('approx_length_cm', 6, 2)->nullable();
            $table->unsignedInteger('approx_weight_grams')->nullable();
            $table->string('description', 1000);
            $table->string('source_reference', 1000)->nullable();
            $table->string('source_version', 100)->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('pregnancy_tips', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->text('description');
            $table->string('icon', 60)->default('heart');
            $table->string('color_hex', 7)->default('#4CAF50');
            $table->unsignedTinyInteger('trimester')->nullable();
            $table->unsignedTinyInteger('week_start')->default(1);
            $table->unsignedTinyInteger('week_end')->default(40);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(false);
            $table->string('source_reference', 1000)->nullable();
            $table->timestamps();

            $table->index(['is_published', 'week_start', 'week_end']);
        });

        Schema::create('pregnancy_symptom_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('pregnancy_profile_id')
                ->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->json('symptoms')->nullable();
            $table->string('mood', 50)->nullable();
            $table->unsignedSmallInteger('fetal_kick_count')->nullable();
            $table->decimal('water_intake_liters', 4, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['pregnancy_profile_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pregnancy_symptom_logs');
        Schema::dropIfExists('pregnancy_tips');
        Schema::dropIfExists('pregnancy_weekly_highlights');
        Schema::dropIfExists('pregnancy_profiles');
    }
};
