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
        Schema::create('noc_forms', function (Blueprint $table) {
            $table->id();
            $table->string('unit_occupied_by');
            $table->string('applicant');
            $table->string('unit_area');
            $table->string('sale_price');
            $table->boolean('cooling_bill_paid')->default(0);
            $table->boolean('service_charge_paid')->default(0);
            $table->boolean('noc_fee_paid')->default(0);
            $table->date('service_charge_paid_till');
            $table->string('cooling_receipt_url');
            $table->string('cooling_soa_url',100);
            $table->string('cooling_clearance_url',100);
            $table->string('payment_receipt_url',100);
            $table->string('status', 100)->default('pending seller sign');
            $table->boolean('verified')->default(0);

            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('verified_by');
            $table->unsignedBigInteger('flat_id');
            $table->foreign('building_id')->references('id')->on('buildings');
            $table->foreign('verified_by')->references('id')->on('users');
            $table->foreign('flat_id')->references('id')->on('flats');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('noc_forms');
    }
};
