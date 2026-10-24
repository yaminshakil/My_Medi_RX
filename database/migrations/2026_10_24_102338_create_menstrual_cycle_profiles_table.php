<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('menstrual_cycle_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();
            $table->date('last_period_start')->nullable();
            $table->unsignedTinyInteger('cycle_length')->default(28);
            $table->unsignedTinyInteger('period_length')->default(5);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['is_active', 'last_period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menstrual_cycle_profiles');
    }
};
