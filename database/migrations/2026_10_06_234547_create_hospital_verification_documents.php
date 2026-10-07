<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('hospital_verification_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hospital_id')
                ->constrained('hospitals')
                ->cascadeOnDelete();

            $table->string('document_type');

            $table->string('document_title')->nullable();

            $table->string('document_number')->nullable();

            $table->string('document_path');

            $table->date('issued_at')->nullable();

            $table->date('expires_at')->nullable();

            $table->enum('verification_status', [
                'pending',
                'approved',
                'rejected',
            ])->default('pending');

            $table->text('rejection_reason')->nullable();

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            $table->index([
                'hospital_id',
                'verification_status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_verification_documents');
    }
};
