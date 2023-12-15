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
        Schema::create('oam_invoice_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('oam_invoice_id');
            $table->json('data'); // Stores the original data of the invoice
            $table->timestamps();

            $table->foreign('oam_invoice_id')->references('id')->on('oam_invoices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_audits');
    }
};
