<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Every table is prefixed "bb_" so the module cannot collide with an existing hospital system.
 * To link the issue log to an existing patients table, point bb_blood_issues.patient_id at it
 * and change $table on the Patient model.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bb_hospitals', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('address')->nullable();
            $t->string('phone', 40)->nullable();
            // Coordinates drive the donor proximity search for emergency requests.
            $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable();
            $t->timestamps();
        });

        Schema::create('bb_storage_units', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('location')->nullable();
            $t->string('type', 40)->default('refrigerator'); // refrigerator | freezer | platelet_agitator
            $t->decimal('min_temp_c', 5, 1)->nullable();
            $t->decimal('max_temp_c', 5, 1)->nullable();
            $t->unsignedSmallInteger('capacity')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('bb_donors', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('name');
            $t->string('phone', 40)->nullable();
            $t->string('email')->nullable();
            $t->string('blood_group', 3)->index();
            $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable();
            $t->string('city')->nullable();
            $t->date('last_donated_at')->nullable();
            $t->boolean('is_verified')->default(false);
            $t->timestamp('verified_at')->nullable();
            $t->boolean('is_available')->default(true); // donor opted in to emergency alerts
            $t->timestamps();

            $t->index(['latitude', 'longitude']);
            $t->index(['is_verified', 'is_available', 'blood_group']);
        });

        Schema::create('bb_patients', function (Blueprint $t) {
            $t->id();
            $t->string('mrn')->unique();                  // medical record number
            $t->string('clinical_file_no')->nullable()->index();
            $t->string('name')->index();
            $t->date('date_of_birth')->nullable();
            $t->string('gender', 20)->nullable();
            $t->string('blood_group', 3)->nullable();     // null = not typed yet
            $t->timestamps();
        });

        Schema::create('bb_physicians', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('license_no')->nullable()->unique();
            $t->string('department')->nullable();
            $t->string('phone', 40)->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('bb_blood_bags', function (Blueprint $t) {
            $t->id();
            $t->string('bag_number')->unique();           // barcode / ISBT number on the bag
            $t->string('batch_number')->index();
            $t->foreignId('donor_id')->nullable()->constrained('bb_donors')->nullOnDelete();
            $t->string('blood_group', 3);
            $t->string('component', 30);
            $t->unsignedSmallInteger('volume_ml');
            $t->dateTime('collected_at');
            $t->dateTime('expires_at')->index();
            $t->foreignId('storage_unit_id')->constrained('bb_storage_units')->restrictOnDelete();
            $t->string('status', 20)->default('available');
            $t->string('discard_reason', 500)->nullable();
            $t->dateTime('discarded_at')->nullable();
            $t->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            // Matches the stock matrix query (status + group + component).
            $t->index(['status', 'blood_group', 'component']);
        });

        Schema::create('bb_emergency_requests', function (Blueprint $t) {
            $t->id();
            $t->string('reference')->unique();
            $t->foreignId('hospital_id')->constrained('bb_hospitals')->restrictOnDelete();
            $t->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $t->string('blood_group', 3);
            $t->string('component', 30);
            $t->unsignedSmallInteger('units_needed');
            $t->string('urgency', 20)->default('urgent');
            $t->decimal('radius_km', 6, 1);
            $t->string('note', 500)->nullable();
            $t->string('status', 20)->default('open')->index();
            $t->unsignedInteger('donors_notified_count')->default(0);
            $t->dateTime('broadcast_at');
            $t->dateTime('expires_at');
            $t->dateTime('closed_at')->nullable();
            $t->timestamps();
        });

        Schema::create('bb_emergency_request_donor', function (Blueprint $t) {
            $t->id();
            $t->foreignId('emergency_request_id')->constrained('bb_emergency_requests')->cascadeOnDelete();
            $t->foreignId('blood_donor_id')->constrained('bb_donors')->cascadeOnDelete();
            $t->decimal('distance_km', 7, 2)->nullable();
            $t->string('channel', 20)->default('mail');
            $t->dateTime('notified_at')->nullable();
            $t->string('response', 20)->default('pending'); // pending | accepted | declined
            $t->dateTime('responded_at')->nullable();

            $t->unique(['emergency_request_id', 'blood_donor_id'], 'bb_emergency_donor_unique');
        });

        Schema::create('bb_blood_issues', function (Blueprint $t) {
            $t->id();
            $t->string('issue_number')->unique();
            // unique(): a physical bag can only ever be issued once.
            $t->foreignId('blood_bag_id')->unique()->constrained('bb_blood_bags')->restrictOnDelete();
            $t->foreignId('patient_id')->constrained('bb_patients')->restrictOnDelete();
            $t->foreignId('physician_id')->constrained('bb_physicians')->restrictOnDelete();
            $t->foreignId('hospital_id')->nullable()->constrained('bb_hospitals')->nullOnDelete();
            $t->foreignId('emergency_request_id')->nullable()->constrained('bb_emergency_requests')->nullOnDelete();
            // restrict: an audit record must never lose the name of who dispensed the unit.
            $t->foreignId('issued_by')->constrained('users')->restrictOnDelete();
            $t->text('indication');
            $t->string('ward', 100)->nullable();
            $t->string('compatibility_override_reason', 500)->nullable();
            $t->dateTime('issued_at')->index();
            $t->dateTime('transfusion_started_at')->nullable();
            $t->dateTime('transfusion_completed_at')->nullable();
            $t->boolean('reaction_noted')->default(false);
            $t->text('reaction_notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'bb_blood_issues', 'bb_emergency_request_donor', 'bb_emergency_requests', 'bb_blood_bags',
            'bb_physicians', 'bb_patients', 'bb_donors', 'bb_storage_units', 'bb_hospitals',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
