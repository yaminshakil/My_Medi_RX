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
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
            $table->uuid('uuid')->unique()->nullable(false)->change();
            $table->string('first_name')->after('uuid');
            $table->string('last_name')->after('first_name');
            $table->string('mobile')->unique()->nullable()->after('email');
            $table->string('google_id')->nullable()->unique();
            $table->string('password')->nullable()->change(); // Password isn't required for Social login
            $table->enum('status', ['active', 'inactive', 'suspended'])
                ->default('active')->after('remember_token');
            $table->index(['name', 'mobile', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
            $table->dropColumn('mobile');
            $table->dropColumn('google_id');
            $table->dropColumn('password');
            $table->dropColumn('status');
        });
    }
};
