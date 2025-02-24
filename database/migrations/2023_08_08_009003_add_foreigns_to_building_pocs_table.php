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
        Schema::table('building_pocs', function (Blueprint $table) {
            $table
                ->foreign('building_id')
                ->references('id')
                ->on('buildings');

            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('building_pocs', function (Blueprint $table) {
            $table->dropForeign(['building_id']);
            $table->dropForeign(['user_id']);
        });
    }
};
