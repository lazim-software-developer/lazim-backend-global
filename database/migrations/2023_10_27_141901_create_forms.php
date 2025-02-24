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
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email', 50);
            $table->string('phone', 20);
            $table->string('type');
            $table->date('moving_date');
            $table->string('moving_time')->nullable();
            $table->string('time_preference')->nullable();

            $table->boolean('approved')->default(false);
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('approved_id')->nullable();
            $table->unsignedBigInteger('flat_id');

            $table->foreign('building_id')->references('id')->on('buildings');
            $table->foreign('approved_id')->references('id')->on('users');
            $table->foreign('flat_id')->references('id')->on('flats');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
