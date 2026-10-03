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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            // For database records
            $table->nullableMorphs('translatable');

            // For static translations
            $table->string('group')->nullable();
            $table->string('key')->nullable();

            $table->string('field')->nullable();
            $table->string('locale', 10);

            $table->text('value');

            $table->timestamps();

            $table->unique(
                ['translatable_type', 'translatable_id', 'field', 'locale'],
                'translations_model_locale_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
