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
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_associations');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();

        });
    }
};
