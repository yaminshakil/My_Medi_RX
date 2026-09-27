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
        Schema::create('pharmacists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            $table->string('registration_number')
                ->nullable();

            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();

            $table->string('qualification')->nullable();

            $table->string('registration_document')
                ->nullable();

            $table->boolean('is_primary')->default(false);

            $table->enum('status', [
                'pending',
                'verified',
                'rejected',
            ])->default('pending');

            $table->timestamp('verified_at')->nullable();

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->index(['pharmacy_id', 'status']);
            $table->index('registration_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacists');
    }
};
