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
        Schema::create('oam_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('flat_id');
            $table->string('invoice_number', 50);
            $table->date('invoice_date');
            $table->string('invoice_status', 100);
            $table->decimal('due_amount', 15, 2);
            $table->decimal('general_fund_amount', 15, 4);
            $table->decimal('reserve_fund_amount', 15, 4);
            $table->decimal('additional_charges', 15, 4)->nullable();
            $table->decimal('previous_balance', 15, 4)->nullable();
            $table->decimal('adjust_amount', 15, 4)->nullable();
            $table->date('invoice_due_date');
            $table->string('invoice_pdf_link')->nullable();
            $table->string('invoice_detail_link')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('building_id')->references('id')->on('buildings');
            $table->foreign('flat_id')->references('id')->on('flats');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oam_invoices');
    }
};
