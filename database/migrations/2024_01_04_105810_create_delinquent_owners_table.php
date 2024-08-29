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
        Schema::create('delinquent_owners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('flat_id');
            $table->unsignedBigInteger('owner_id');
            $table->date('last_payment_date')->nullable();
            $table->decimal('last_payment_amount', 15, 2)->nullable();
            $table->decimal('outstanding_balance', 15, 2)->nullable();
            $table->decimal('quarter_1_balance', 15, 2)->nullable();
            $table->decimal('quarter_2_balance', 15, 2)->nullable();
            $table->decimal('quarter_3_balance', 15, 2)->nullable();
            $table->decimal('quarter_4_balance', 15, 2)->nullable();
            $table->string('invoice_pdf_link')->nullable();
            $table->bigInteger('year');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delinquent_owners');
    }
};
