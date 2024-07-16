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
        Schema::create('building_poll', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('building_id')->nullable();
            $table->unsignedBigInteger('poll_id')->nullable();
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('building_id')->references('id')->on('buildings');
            $table->foreign('poll_id')->references('id')->on('polls');
            $table->foreign('owner_association_id')->references('id')->on('owner_associations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('building_poll');
    }
};
