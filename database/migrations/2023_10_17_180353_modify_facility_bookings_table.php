<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('facility_bookings', function (Blueprint $table) {
            $table->json('remarks')->nullable()->change();
            $table->json('reference_number')->nullable()->change();
            $table->boolean('approved')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('facility_bookings', function (Blueprint $table) {
            $table->json('remarks')->change();
            $table->json('reference_number')->change();
            $table->boolean('approved')->change();
        });
    }
};
