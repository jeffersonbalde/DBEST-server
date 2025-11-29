<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update inventory_items status enum
        DB::statement("ALTER TABLE inventory_items MODIFY COLUMN status ENUM('SERVICEABLE', 'UNSERVICEABLE', 'NEEDS REPAIR', 'MISSING/LOST') DEFAULT 'SERVICEABLE'");
        
        // Map old values to new values
        DB::table('inventory_items')->where('status', 'available')->update(['status' => 'SERVICEABLE']);
        DB::table('inventory_items')->where('status', 'assigned')->update(['status' => 'SERVICEABLE']);
        DB::table('inventory_items')->where('status', 'maintenance')->update(['status' => 'NEEDS REPAIR']);
        DB::table('inventory_items')->where('status', 'disposed')->update(['status' => 'UNSERVICEABLE']);
        
        // Update dcp_inventory_items condition_status
        DB::table('dcp_inventory_items')->where('condition_status', 'Working')->update(['condition_status' => 'SERVICEABLE']);
        DB::table('dcp_inventory_items')->where('condition_status', 'For Repair')->update(['condition_status' => 'NEEDS REPAIR']);
        DB::table('dcp_inventory_items')->where('condition_status', 'For Part Replacement')->update(['condition_status' => 'NEEDS REPAIR']);
        DB::table('dcp_inventory_items')->where('condition_status', 'Unrepairable')->update(['condition_status' => 'UNSERVICEABLE']);
        DB::table('dcp_inventory_items')->where('condition_status', 'Lost')->update(['condition_status' => 'MISSING/LOST']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Map new values back to old values
        DB::table('inventory_items')->where('status', 'SERVICEABLE')->update(['status' => 'available']);
        DB::table('inventory_items')->where('status', 'NEEDS REPAIR')->update(['status' => 'maintenance']);
        DB::table('inventory_items')->where('status', 'UNSERVICEABLE')->update(['status' => 'disposed']);
        DB::table('inventory_items')->where('status', 'MISSING/LOST')->update(['status' => 'disposed']);
        
        // Revert enum
        DB::statement("ALTER TABLE inventory_items MODIFY COLUMN status ENUM('available', 'assigned', 'maintenance', 'disposed') DEFAULT 'available'");
        
        // Revert DCP inventory
        DB::table('dcp_inventory_items')->where('condition_status', 'SERVICEABLE')->update(['condition_status' => 'Working']);
        DB::table('dcp_inventory_items')->where('condition_status', 'NEEDS REPAIR')->update(['condition_status' => 'For Repair']);
        DB::table('dcp_inventory_items')->where('condition_status', 'UNSERVICEABLE')->update(['condition_status' => 'Unrepairable']);
        DB::table('dcp_inventory_items')->where('condition_status', 'MISSING/LOST')->update(['condition_status' => 'Lost']);
    }
};

