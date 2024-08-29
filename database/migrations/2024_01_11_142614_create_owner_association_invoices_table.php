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
        Schema::create('owner_association_invoices', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->date('due_date')->nullable();
            $table->string('invoice_number');
            $table->string('type');
            $table->string('bill_to')->nullable();
            $table->string('address')->nullable();
            $table->string('trn')->nullable();
            $table->string('mode_of_payment')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('job');
            $table->string('description');
            $table->string('month');
            $table->integer('quantity');
            $table->bigInteger('rate');
            $table->integer('tax');
            $table->unsignedBigInteger('building_id')->nullable();
            $table->unsignedBigInteger('owner_association_id');

            $table->foreign('building_id')->references('id')->on('buildings');
            $table->foreign('owner_association_id')->references('id')->on('owner_associations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_association_invoices');
    }
};
