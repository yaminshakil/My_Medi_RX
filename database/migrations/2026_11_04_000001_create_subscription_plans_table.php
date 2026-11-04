<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('slug')->unique();
            $table->text('description')->nullable(); $table->decimal('price', 10, 2);
            $table->string('billing_cycle', 20); $table->unsignedInteger('duration_days');
            $table->string('user_type', 30)->default('All'); $table->json('features')->nullable();
            $table->boolean('is_active')->default(true); $table->timestamps();
            $table->index(['user_type', 'is_active']);
        });
    }
    public function down(): void { Schema::dropIfExists('subscription_plans'); }
};
