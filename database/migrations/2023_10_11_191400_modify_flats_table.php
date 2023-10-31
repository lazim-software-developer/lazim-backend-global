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
        Schema::table('flats', function (Blueprint $table) {
            // drop number column
            $table->dropColumn('number');
            
            // Modify the 'property_number' column to be a string of length 50
            $table->string('property_number', 50);
            
            // Add the 'mollak_property_id' column
            $table->string('mollak_property_id', 50);
            
            // Add the 'property_type' column
            $table->string('property_type', 50);
            
            // Make the 'floor' column nullable
            $table->string('floor')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flats', function (Blueprint $table) {
            $table->text('number');
            
            // Drop the 'mollak_property_id' and 'property_type' columns
            $table->dropColumn(['mollak_property_id', 'property_type']);
        });
    }
};
