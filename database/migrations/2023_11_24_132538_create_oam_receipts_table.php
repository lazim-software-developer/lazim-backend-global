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
        Schema::create('oam_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number');
            $table->dateTime('receipt_date');
            $table->string('record_source');
            $table->decimal('receipt_amount', 15, 2);
            $table->dateTime('receipt_created_date');
            $table->string('transaction_reference')->nullable();
            $table->string('payment_mode');
            $table->string('virtual_account_description')->nullable();
            $table->json('noqodi_info')->nullable();
            $table->string('payment_status');
            $table->date('from_date');
            $table->date('to_date');
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('flat_id');
            $table->timestamps();

            $table->foreign('building_id')->references('id')->on('buildings');
            $table->foreign('flat_id')->references('id')->on('flats');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oam_receipts');
    }
};
