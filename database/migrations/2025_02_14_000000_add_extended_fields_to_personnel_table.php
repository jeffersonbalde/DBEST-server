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
        Schema::table('personnel', function (Blueprint $table) {
            $table->string('id_number')->nullable()->after('employee_id');
            $table->string('employment_status')->nullable()->after('id_number');
            $table->string('employment_level')->nullable()->after('employment_status');
            $table->string('rating')->nullable()->after('employment_level');
            $table->text('notes')->nullable()->after('subject_area');
            $table->string('password')->after('type');

            $table->unique('id_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnel', function (Blueprint $table) {
            $table->dropUnique(['id_number']);
            $table->dropColumn([
                'id_number',
                'employment_status',
                'employment_level',
                'rating',
                'notes',
                'password',
            ]);
        });
    }
};


