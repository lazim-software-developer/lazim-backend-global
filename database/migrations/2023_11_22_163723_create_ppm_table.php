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
        Schema::create('ppm', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->integer('quarter');
            $table->date('date');
            $table->string('job_description')->nullable();
            $table->string('document', 100);
            $table->unsignedBigInteger('created_by');
            $table->string('status')->nullable();
            $table->string('remarks')->nullable();
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('status_updated_by')->nullable();

            $table->foreign('asset_id')->references('id')->on('assets');
            $table->foreign('building_id')->references('id')->on('buildings');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('status_updated_by')->references('id')->on('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppm');
    }
};
