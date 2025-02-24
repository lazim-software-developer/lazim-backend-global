<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('building_facility', function (Blueprint $table) {
            $table
                ->foreign('facility_id')
                ->references('id')
                ->on('facilities');

            $table
                ->foreign('building_id')
                ->references('id')
                ->on('buildings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('building_facility', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
            $table->dropForeign(['building_id']);
        });
    }
};
