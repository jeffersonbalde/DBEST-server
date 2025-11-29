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
        Schema::table('personnel', function (Blueprint $table) {
            if (!Schema::hasColumn('personnel', 'username')) {
                $table->string('username')->nullable()->after('employee_id');
            }
        });

        // Ensure existing rows have a username value before adding the unique constraint
        DB::table('personnel')
            ->whereNull('username')
            ->orWhere('username', '')
            ->update(['username' => DB::raw('employee_id')]);

        Schema::table('personnel', function (Blueprint $table) {
            if (Schema::hasColumn('personnel', 'username')) {
                $table->unique('username');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnel', function (Blueprint $table) {
            if (Schema::hasColumn('personnel', 'username')) {
                $table->dropUnique(['username']);
                $table->dropColumn('username');
            }
        });
    }
};



