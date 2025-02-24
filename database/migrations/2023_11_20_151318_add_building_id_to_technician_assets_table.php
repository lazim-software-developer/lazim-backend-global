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
        Schema::table('technician_assets', function (Blueprint $table) {
            // Add the building_id column
            $table->unsignedBigInteger('building_id')->nullable()->after('vendor_id');

            // Set up the foreign key relationship
            $table->foreign('building_id')->references('id')->on('buildings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technician_assets', function (Blueprint $table) {
            // Remove the foreign key constraint
            $table->dropForeign(['building_id']);

            // Remove the building_id column
            $table->dropColumn('building_id');
        });
    }
};
