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
        Schema::table('service_vendor', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('active')->default(true);

            $table->unsignedBigInteger('building_id')->nullable();
            $table->foreign('building_id')->references('id')->on('buildings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_vendor', function (Blueprint $table) {
            $table->dropColumn('active');
            $table->dropColumn('end_date');
            $table->dropColumn('start_date');
            $table->dropColumn('id');
            $table->dropForeign(['building_id']);
            $table->dropColumn('building_id');
        });
    }
};
