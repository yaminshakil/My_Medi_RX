<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->restrictOnDelete();
            $table->timestamp('starts_at')->nullable(); $table->timestamp('ends_at')->nullable();
            $table->string('status', 20)->default('pending'); $table->boolean('auto_renew')->default(false);
            $table->timestamps(); $table->index(['user_id', 'status']); $table->index('ends_at');
        });
    }
    public function down(): void { Schema::dropIfExists('subscriptions'); }
};
