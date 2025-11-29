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
        Schema::create('dcp_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('batch_name');
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('package_count')->default(1);
            $table->date('delivery_date')->nullable();
            $table->string('delivery_status')->nullable();
            $table->string('installation_status')->nullable();
            $table->text('details')->nullable();
            $table->text('remarks')->nullable();
            $table->string('dr_number')->nullable();
            $table->string('dr_filename')->nullable();
            $table->string('ptr_number')->nullable();
            $table->string('ptr_filename')->nullable();
            $table->string('iar_number')->nullable();
            $table->string('iar_filename')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('icts')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dcp_packages');
    }
};

