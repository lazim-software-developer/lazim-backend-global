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
        Schema::create('wda', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('job_description');
            $table->string('document', 100);
            $table->unsignedBigInteger('created_by');
            $table->string('status')->default('pending');
            $table->string('remarks')->nullable();
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('contract_id');
            $table->unsignedBigInteger('status_updated_by')->nullable();
            $table->unsignedBigInteger('vendor_id');

            $table->foreign('building_id')->references('id')->on('buildings');
            $table->foreign('vendor_id')->references('id')->on('vendors');
            $table->foreign('contract_id')->references('id')->on('contracts');
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
        Schema::dropIfExists('wda');
    }
};
