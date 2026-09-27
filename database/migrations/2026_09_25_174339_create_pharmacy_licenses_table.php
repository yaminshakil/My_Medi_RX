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
        Schema::create('pharmacy_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('license_number');
            $table->string('license_type')->default('drug_license');

            $table->date('issued_date')->nullable();
            $table->date('expiry_date')->nullable();

            $table->string('document')->nullable();

            $table->enum('status', [
                'pending',
                'verified',
                'rejected',
                'expired',
            ])->default('pending');

            $table->timestamp('verified_at')->nullable();

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->index('license_number');
            $table->index(['pharmacy_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_licenses');
    }
};
