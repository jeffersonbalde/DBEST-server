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
        Schema::create('dcp_inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('dcp_package_id')
                ->constrained('dcp_packages')
                ->cascadeOnDelete();
            $table->string('batch_name')->nullable();
            $table->string('category');
            $table->string('description');
            $table->string('manufacturer');
            $table->string('model');
            $table->string('serial_number');
            $table->string('unit_of_measure')->nullable();
            $table->decimal('unit_value', 12, 2)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('property_no');
            $table->foreignId('personnel_id')
                ->nullable()
                ->constrained('personnel')
                ->nullOnDelete();
            $table->string('personnel_name')->nullable();
            $table->string('personnel_position')->nullable();
            $table->string('condition_status')->default('Working');
            $table->date('last_checked_at')->nullable();
            $table->string('validation_status')->default('Unverified');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dcp_inventory_items');
    }
};


