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
        Schema::table('property_custodians', function (Blueprint $table) {
            $table->string('username')->unique()->after('employee_id');
            $table->foreignId('school_id')->nullable()->after('username')->constrained('schools')->nullOnDelete();
            $table->string('avatar_path')->nullable()->after('phone');
        });

        // Allow employee_id and email to be nullable since username will be primary credential
        DB::statement('ALTER TABLE property_custodians MODIFY employee_id VARCHAR(255) NULL');
        DB::statement('ALTER TABLE property_custodians MODIFY email VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE property_custodians MODIFY employee_id VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE property_custodians MODIFY email VARCHAR(255) NOT NULL');

        Schema::table('property_custodians', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropColumn(['username', 'school_id', 'avatar_path']);
        });
    }
};

