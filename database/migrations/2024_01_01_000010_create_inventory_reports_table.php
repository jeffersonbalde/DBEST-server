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
        Schema::create('inventory_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generated_by')->nullable()->constrained('property_custodians')->onDelete('set null');
            $table->string('report_type'); // inventory_summary, assigned_items, category_breakdown, etc.
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('filters')->nullable(); // Store filter criteria
            $table->json('data')->nullable(); // Store report data
            $table->string('file_path')->nullable(); // If exported to file
            $table->enum('format', ['json', 'pdf', 'excel'])->default('json');
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_reports');
    }
};

