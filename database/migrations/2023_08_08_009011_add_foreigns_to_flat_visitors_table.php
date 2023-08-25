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
                ->on('flats')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table
                ->foreign('initiated_by')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table
                ->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table
                ->foreign('building_id')
                ->references('id')
                ->on('buildings')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
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
        });
    }
};
