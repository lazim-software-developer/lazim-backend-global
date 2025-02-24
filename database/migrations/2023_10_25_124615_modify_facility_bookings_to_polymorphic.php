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
        Schema::table('facility_bookings', function (Blueprint $table) {
            // Rename facility_id to bookable_id
            $table->renameColumn('facility_id', 'bookable_id');

            // Add bookable_type column
            $table->string('bookable_type');

            // Drop foreign key constraint for facility_id if exists
            $table->dropForeign(['facility_id']); // The exact constraint name might differ, adjust accordingly
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::
        table('facility_bookings', function (Blueprint $table) {
            // Rename bookable_id back to facility_id
            $table->renameColumn('bookable_id', 'facility_id');

            // Drop bookable_type column
            $table->dropColumn('bookable_type');

            // Add foreign key constraint back to facility_id if needed
            $table->foreign('facility_id')->references('id')->on('facilities');
        });
    }
};
