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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('contract_id');
            $table->string('invoice_number', 100);
            $table->unsignedBigInteger('wda_id');
            $table->date('date');
            $table->string('document', 100);
            $table->unsignedBigInteger('created_by');
            $table->string('status')->default('pending');
            $table->string('remarks')->nullable();
            $table->unsignedBigInteger('status_updated_by')->nullable();
            $table->unsignedBigInteger('vendor_id');
            $table->decimal('invoice_amount', 15, 2); // Assuming standard decimal format

            $table->foreign('building_id')->references('id')->on('buildings');
            $table->foreign('contract_id')->references('id')->on('contracts');
            $table->foreign('wda_id')->references('id')->on('wda');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('status_updated_by')->references('id')->on('users');
            $table->foreign('vendor_id')->references('id')->on('vendors');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
