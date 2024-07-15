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
        Schema::table('polls', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['building_id']);
    
            // Change the column
            $table->bigInteger('building_id')->nullable()->change();
    
            // Re-add the foreign key constraint if necessary
            $table->foreign('building_id')->references('id')->on('buildings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->dropForeign(['building_id']);
    
            $table->unsignedBigInteger('building_id')->nullable(false)->change();
    
            $table->foreign('building_id')->references('id')->on('buildings');
        });
    }
};
