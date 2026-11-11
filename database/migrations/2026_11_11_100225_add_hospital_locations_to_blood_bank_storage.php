<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bb_storage_units', function (Blueprint $table) {
            $table->foreignId('hospital_id')
                ->nullable()
                ->constrained('hospitals')
                ->nullOnDelete();
        });

        Schema::table('bb_donors', function (Blueprint $table) {
            $table->timestamp('consented_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bb_storage_units', function (Blueprint $table) {
            $table->dropForeign(['hospital_id']);
            $table->dropColumn('hospital_id');
        });

        Schema::table('bb_donors', function (Blueprint $table) {
            $table->dropColumn('consented_at');
        });
    }
};
