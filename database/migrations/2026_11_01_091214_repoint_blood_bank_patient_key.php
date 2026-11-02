<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bb_blood_issues', function (Blueprint $t) {
            $t->dropForeign(['patient_id']);
            $t->foreign('patient_id')->references('id')->on('patients')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bb_blood_issues', function (Blueprint $t) {
            $t->dropForeign(['patient_id']);
            $t->foreign('patient_id')->references('id')->on('bb_patients')->restrictOnDelete();
        });
    }
};
