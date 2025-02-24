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
        Schema::table('access_cards', function (Blueprint $table) {
            $table->unsignedBigInteger('building_id')->after('user_id');
            $table->foreign('building_id')->references('id')->on('buildings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('access_cards', function (Blueprint $table) {
            $table->dropForeign(['building_id']);
            $table->dropColumn('building_id'); 
        });
    }
};
