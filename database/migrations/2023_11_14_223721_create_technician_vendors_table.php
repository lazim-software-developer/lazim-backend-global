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
        Schema::create('technician_vendors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('technician_id');
            $table->unsignedBigInteger('vendor_id');
            $table->boolean('active')->default(true);
            $table->string('position')->nullable();

            $table->foreign('technician_id')->references('id')->on('users');
            $table->foreign('vendor_id')->references('id')->on('vendors');
            $table->timestamps();
        });


        Schema::create('service_technician_vendor', function (Blueprint $table) {
            $table->unsignedBigInteger('technician_vendor_id');
            $table->unsignedBigInteger('service_id');

            $table->foreign('technician_vendor_id')->references('id')->on('technician_vendors');
            $table->foreign('service_id')->references('id')->on('services');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technician_vendors');
    }
};
