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
        Schema::table('flat_visitors', function (Blueprint $table) {
            $table
                ->foreign('flat_id')
                ->references('id')
                ->on('flats');

            $table
                ->foreign('initiated_by')
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
        Schema::table('flat_visitors', function (Blueprint $table) {
            $table->dropForeign(['flat_id']);
            $table->dropForeign(['initiated_by']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['building_id']);
        });
    }
};
