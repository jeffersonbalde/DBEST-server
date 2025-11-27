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
        Schema::create('assigned_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('property_custodians')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->date('assigned_date');
            $table->date('return_date')->nullable();
            $table->enum('status', ['active', 'returned', 'lost', 'damaged'])->default('active');
            $table->text('condition_notes')->nullable();
            $table->text('return_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assigned_items');
    }
};

