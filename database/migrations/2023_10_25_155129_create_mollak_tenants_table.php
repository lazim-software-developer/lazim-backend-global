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
        Schema::create('mollak_tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contract_number')->nullable();
            $table->string('emirates_id')->nullable();
            $table->string('license_number', 50)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->string('contract_status', 50)->nullable();
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('flat_id')->nullable();

            $table->foreign('building_id')->references('id')->on('buildings');
            $table->foreign('flat_id')->references('id')->on('flats');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mollak_tenants');
    }
};
