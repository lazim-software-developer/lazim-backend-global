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
        if (!Schema::hasColumn('apartment_owners', 'building_id')) {
            Schema::table('apartment_owners', function (Blueprint $table) {
                $table->unsignedBigInteger('building_id')->nullable();
                $table->foreign('building_id')->references('id')->on('buildings');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apartment_owners', function (Blueprint $table) {
            $table->dropColumn('building_id');
        });
    }
};
