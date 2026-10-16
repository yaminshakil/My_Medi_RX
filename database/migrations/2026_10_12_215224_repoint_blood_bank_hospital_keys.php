<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Emergency requests must reference your real hospitals table.
        Schema::table('bb_emergency_requests', function (Blueprint $t) {
            $t->dropForeign(['hospital_id']);
            $t->foreign('hospital_id')->references('id')->on('hospitals')->restrictOnDelete();
        });

        Schema::table('bb_blood_issues', function (Blueprint $t) {
            $t->dropForeign(['hospital_id']);
            $t->foreign('hospital_id')->references('id')->on('hospitals')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bb_emergency_requests', function (Blueprint $t) {
            $t->dropForeign(['hospital_id']);
            $t->foreign('hospital_id')->references('id')->on('bb_hospitals')->restrictOnDelete();
        });

        Schema::table('bb_blood_issues', function (Blueprint $t) {
            $t->dropForeign(['hospital_id']);
            $t->foreign('hospital_id')->references('id')->on('bb_hospitals')->nullOnDelete();
        });
    }
};
