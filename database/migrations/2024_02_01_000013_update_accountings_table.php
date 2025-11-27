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
        Schema::table('accountings', function (Blueprint $table) {
            $table->string('username')->unique()->after('employee_id');
            $table->string('avatar_path')->nullable()->after('phone');
        });

        DB::statement('ALTER TABLE accountings MODIFY employee_id VARCHAR(255) NULL');
        DB::statement('ALTER TABLE accountings MODIFY email VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE accountings MODIFY employee_id VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE accountings MODIFY email VARCHAR(255) NOT NULL');

        Schema::table('accountings', function (Blueprint $table) {
            $table->dropColumn(['username', 'avatar_path']);
        });
    }
};

