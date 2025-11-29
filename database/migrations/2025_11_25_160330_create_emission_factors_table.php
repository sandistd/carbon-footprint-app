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
        Schema::create('emission_factors', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Solar (Diesel)", "Bensin (Pertalite)", "Listrik PLN"
            $table->string('scope'); // scope_1, scope_2, scope_3
            $table->string('category')->nullable(); // e.g., "Stationary Combustion", "Mobile Combustion"
            $table->decimal('factor', 10, 4); // Emission factor value (e.g., 2.68 for diesel)
            $table->string('unit'); // e.g., "kg CO2eq/Liter", "kg CO2eq/KWh"
            $table->text('description')->nullable();
            $table->string('source')->nullable(); // e.g., "GHG Protocol 2004"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emission_factors');
    }
};
