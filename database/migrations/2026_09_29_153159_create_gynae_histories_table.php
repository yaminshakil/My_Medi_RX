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
        Schema::create('gynae_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->onDelete('cascade');

            // Marriage Details
            $table->string('marital_status')->nullable(); // Married/Unmarried/Widow
            $table->string('marriage_duration')->nullable(); // e.g. 5 years
            $table->string('consanguinity')->nullable(); // Yes/No

            // Menstrual History
            $table->string('menarche_age')->nullable();
            $table->date('lmp')->nullable(); // last menstrual period
            $table->string('cycle')->nullable(); // e.g. 28 days
            $table->string('flow')->nullable(); // normal/heavy/scanty
            $table->boolean('dysmenorrhea')->default(false); // painful periods
            $table->boolean('contraceptive_use')->default(false);

            // Obstetrical History
            $table->integer('gravida')->nullable(); // total pregnancies
            $table->integer('para')->nullable();    // live births
            $table->integer('abortion')->nullable();
            $table->integer('living_children')->nullable();

            $table->date('edd')->nullable(); // Expected Date of Delivery
            $table->string('anc')->nullable(); // Antenatal Checkups

            $table->text('other_history')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gynae_histories');
    }
};
