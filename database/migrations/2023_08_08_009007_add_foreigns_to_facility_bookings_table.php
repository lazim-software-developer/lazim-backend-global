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
            $table
                ->foreign('facility_id')
                ->references('id')
                ->on('facilities');

            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users');

            $table
                ->foreign('approved_by')
                ->references('id')
                ->on('users');

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
        Schema::table('facility_bookings', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['building_id']);
        });
    }
};
