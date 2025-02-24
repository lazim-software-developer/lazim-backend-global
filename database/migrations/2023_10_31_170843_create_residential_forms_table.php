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
        Schema::create('residential_forms', function (Blueprint $table) {
            $table->id();
            $table->string('unit_occupied_by');
            $table->string('name');
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('flat_id');
            $table->string('passport_number')->nullable();
            $table->integer('number_of_adults')->default(0);
            $table->integer('number_of_children')->default(0);
            $table->string('office_number')->nullable();
            $table->string('trn_number')->nullable();
            $table->date('passport_expires_on')->nullable();
            $table->string('emirates_id')->nullable();
            $table->date('emirates_expires_on')->nullable();
            $table->string('title_deed_number')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->json('emergency_contact');
            $table->string('passport_url')->nullable();
            $table->string('emirates_url')->nullable();
            $table->string('title_deed_url')->nullable();

            $table->foreign('building_id')->references('id')->on('buildings');
            $table->foreign('flat_id')->references('id')->on('flats');
            $table->foreign('user_id')->references('id')->on('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('residential_forms');
    }
};
