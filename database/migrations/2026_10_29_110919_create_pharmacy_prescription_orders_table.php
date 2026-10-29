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
        Schema::create('pharmacy_prescription_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prescription_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('status', ['received', 'processing', 'dispensed', 'cancelled'])
                ->default('received');
            $table->timestamp('received_at');
            $table->timestamp('dispensed_at')->nullable();
            $table->timestamps();

            $table->index(['pharmacy_id', 'status', 'received_at'], 'pharm_rx_orders_pharmacy_status_received_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_prescription_orders');
    }
};
