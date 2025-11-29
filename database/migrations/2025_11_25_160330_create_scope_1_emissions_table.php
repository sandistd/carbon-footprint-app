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
        Schema::create('scope_1_emissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emission_factor_id')->constrained()->onDelete('cascade');
            $table->foreignId('stakeholder_id')->nullable()->constrained()->onDelete('set null');
            $table->date('measurement_date');
            $table->decimal('activity_value', 15, 2); // e.g., volume in liters
            $table->string('activity_unit'); // e.g., "Liter"
            $table->decimal('emission_result', 15, 2)->default(0); // calculated emission in Ton CO2eq
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scope_1_emissions');
    }
};
