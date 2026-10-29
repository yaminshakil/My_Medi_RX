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
        Schema::create('pharmacy_dispenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pharmacy_prescription_order_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('pharmacy_inventory_item_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('dispensed_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamp('dispensed_at');
            $table->timestamps();

            $table->index(['pharmacy_id', 'dispensed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_dispenses');
    }
};
