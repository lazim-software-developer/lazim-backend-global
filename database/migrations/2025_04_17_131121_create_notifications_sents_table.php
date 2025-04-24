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
        Schema::create('notifications_sents', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('building_id')->nullable();
            $table->integer('owner_association_id')->nullable();
            $table->integer('sale_noc_id')->nullable();
            $table->integer('service_id')->nullable();
            $table->integer('facility_booking_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications_sents');
    }
};
