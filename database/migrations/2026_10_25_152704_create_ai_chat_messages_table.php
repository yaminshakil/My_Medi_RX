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
        Schema::create('ai_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_chat_session_id')
        ->constrained('ai_chat_sessions')
        ->cascadeOnDelete();

            $table->enum('sender', ['user', 'ai']);
            $table->text('message');
            $table->json('suggested_actions')->nullable();
            $table->timestamps();

            $table->index(['ai_chat_session_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_chat_messages');
    }
};
