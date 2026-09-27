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
        Schema::create('pharmacy_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();

            $table->string('division')->nullable();
            $table->string('district')->nullable();
            $table->string('upazila')->nullable();
            $table->string('union')->nullable();

            $table->string('postal_code', 20)->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->boolean('is_primary')->default(true);

            $table->timestamps();

            $table->index(['district', 'upazila']);
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_addresses');
    }
};
