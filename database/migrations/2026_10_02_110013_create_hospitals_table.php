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
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('hospital_type')->nullable();
            $table->string('hospital_name');
            $table->string('mobile_number');
            $table->string('emergency_contact')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('address');
            $table->string('registration_no')->nullable();
            $table->string('service_time')->nullable();
            $table->string('organization_notice')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('thana_id')->nullable()->constrained('thanas')->onDelete('set null');
            $table->foreignId('district_id')->nullable()->constrained('districts')->onDelete('set null');
            $table->foreignId('division_id')->nullable()->constrained('divisions')->onDelete('set null');
            $table->string('hospital_logo')->nullable();
            $table->json('logo_crop_data')->nullable();
            $table->string('banner_url')->nullable();
            $table->json('banner_crop_data')->nullable();
            $table->string('hospital_url')->nullable();
            $table->text('hospital_description')->nullable();
            $table->integer('sort_order')->nullable();
            $table->integer('status')->nullable();
            $table->enum('verification_status', [
                'pending',
                'under_review',
                'approved',
                'rejected',
                'suspended',
            ])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->integer('verified_by')->nullable();
            $table->text('verification_note')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospitals');
    }
};
