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
        Schema::create('legal_notice', function (Blueprint $table) {
            $table->id();
            $table->string('legalNoticeId');
            $table->unsignedBigInteger('building_id')->nullable();
            $table->unsignedBigInteger('flat_id')->nullable();
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->string('mollakPropertyId')->nullable();
            $table->dateTime('registrationDate');
            $table->string('registrationNumber');
            $table->string('invoicePeriod');
            $table->bigInteger('previousBalance');
            $table->bigInteger('invoiceAmount');
            $table->bigInteger('approvedLegalAmount');
            $table->text('legalNoticePDF');
            $table->boolean('isRDCCaseStart');
            $table->boolean('isRDCCaseEnd');

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
        Schema::dropIfExists('legal_notice');
    }
};
