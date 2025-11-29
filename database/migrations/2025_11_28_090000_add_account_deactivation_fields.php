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
            $table->string('avatar_path')->nullable()->after('phone');
            $table->text('deactivation_reason')->nullable()->after('notes');
            $table->string('deactivated_by')->nullable()->after('deactivation_reason');
            $table->timestamp('deactivated_at')->nullable()->after('deactivated_by');
        });

        Schema::table('property_custodians', function (Blueprint $table) {
            $table->text('deactivation_reason')->nullable()->after('position');
            $table->string('deactivated_by')->nullable()->after('deactivation_reason');
            $table->timestamp('deactivated_at')->nullable()->after('deactivated_by');
        });

        Schema::table('accountings', function (Blueprint $table) {
            $table->text('deactivation_reason')->nullable()->after('phone');
            $table->string('deactivated_by')->nullable()->after('deactivation_reason');
            $table->timestamp('deactivated_at')->nullable()->after('deactivated_by');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->text('deactivation_reason')->nullable()->after('subject_area');
            $table->string('deactivated_by')->nullable()->after('deactivation_reason');
            $table->timestamp('deactivated_at')->nullable()->after('deactivated_by');
        });

        Schema::table('icts', function (Blueprint $table) {
            $table->text('deactivation_reason')->nullable()->after('position');
            $table->string('deactivated_by')->nullable()->after('deactivation_reason');
            $table->timestamp('deactivated_at')->nullable()->after('deactivated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnel', function (Blueprint $table) {
            $table->dropColumn(['avatar_path', 'deactivation_reason', 'deactivated_by', 'deactivated_at']);
        });

        Schema::table('property_custodians', function (Blueprint $table) {
            $table->dropColumn(['deactivation_reason', 'deactivated_by', 'deactivated_at']);
        });

        Schema::table('accountings', function (Blueprint $table) {
            $table->dropColumn(['deactivation_reason', 'deactivated_by', 'deactivated_at']);
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['deactivation_reason', 'deactivated_by', 'deactivated_at']);
        });

        Schema::table('icts', function (Blueprint $table) {
            $table->dropColumn(['deactivation_reason', 'deactivated_by', 'deactivated_at']);
        });
    }
};


