<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pediatric_dose_rules', function (Blueprint $table) {
            $table->string('status', 30)->default('draft')->index();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('submitted_at')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->unsignedInteger('version')->default(1);
            $table->foreignId('supersedes_rule_id')
                ->nullable()
                ->constrained('pediatric_dose_rules')
                ->nullOnDelete();
        });

        // Preserve the status of rules that were approved before this migration.
        DB::table('pediatric_dose_rules')
            ->whereNotNull('approved_at')
            ->update(['status' => 'approved']);

        DB::table('pediatric_dose_rules')
            ->whereNull('approved_at')
            ->update(['status' => 'draft']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pediatric_dose_rules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropConstrainedForeignId('supersedes_rule_id');

            $table->dropColumn([
                'status',
                'submitted_at',
                'reviewed_at',
                'rejection_reason',
                'version',
            ]);
        });
    }
};
