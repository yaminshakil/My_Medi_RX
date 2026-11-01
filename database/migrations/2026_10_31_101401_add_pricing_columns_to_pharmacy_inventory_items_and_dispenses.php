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
        Schema::table('pharmacy_inventory_items', function (Blueprint $table) {
            $table->decimal('regular_price', 10, 2)->default(0)->after('strength');
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('regular_price');
        });

        Schema::table('pharmacy_dispenses', function (Blueprint $table) {
            $table->decimal('unit_regular_price', 10, 2)->nullable()->after('quantity');
            $table->decimal('discount_percentage', 5, 2)->nullable()->after('unit_regular_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pharmacy_dispenses', function (Blueprint $table) {
            $table->dropColumn(['unit_regular_price', 'discount_percentage']);
        });

        Schema::table('pharmacy_inventory_items', function (Blueprint $table) {
            $table->dropColumn(['regular_price', 'discount_percentage']);
        });
    }
};
