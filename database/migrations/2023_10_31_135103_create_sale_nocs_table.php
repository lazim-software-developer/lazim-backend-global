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
        Schema::create('sale_nocs', function (Blueprint $table) {
            $table->id();
            $table->enum('unit_occupied_by', ['owner', 'tenant', 'vacant']);
            $table->string('applicant', 100);
            $table->string('unit_area', 100);
            $table->string('sale_price');
            $table->boolean('cooling_bill_paid')->default(0);
            $table->boolean('service_charge_paid')->default(0);
            $table->boolean('noc_fee_paid')->default(0);
            $table->date('service_charge_paid_till');
            $table->string('cooling_receipt', 100);
            $table->string('cooling_soa', 100);
            $table->string('cooling_clearance', 100);
            $table->string('payment_receipt', 100);
            $table->string('status', 100);
            $table->boolean('verified')->default(0);
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('flat_id');
            $table->unsignedBigInteger('user_id');

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('verified_by')->references('id')->on('users');
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
        Schema::dropIfExists('sale_nocs');
    }
};
