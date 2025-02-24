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
        Schema::create('owner_association_receipts', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('receipt_number');
            $table->string('type');
            $table->string('paid_by');
            $table->string('payment_method');
            $table->string('received_in');
            $table->decimal('amount', 15, 2);
            $table->string('receipt_to')->nullable();
            $table->string('payment_reference');
            $table->string('on_account_of');
            $table->string('receipt_document')->nullable();
            $table->unsignedBigInteger('building_id')->nullable();
            $table->unsignedBigInteger('flat_id')->nullable();
            $table->unsignedBigInteger('owner_association_id');

            $table->foreign('building_id')->references('id')->on('buildings');
            $table->foreign('flat_id')->references('id')->on('flats');
            $table->foreign('owner_association_id')->references('id')->on('owner_associations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_association_receipts');
    }
};
