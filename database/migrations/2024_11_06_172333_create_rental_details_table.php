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
        Schema::create('rental_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flat_id')->constrained('flats');
            $table->enum('number_of_cheques', [1, 2, 3, 4, 6]);
            $table->date('contract_start_date');
            $table->date('contract_end_date');
            $table->double('admin_fee', 10, 2)->nullable();
            $table->double('other_charges', 10, 2)->nullable();
            $table->double('advance_amount', 10, 2);
            $table->enum('advance_amount_payment_mode', ['Online', 'Cheque', 'Cash']);
            $table->enum('status', ['Expired', 'Contract extended', 'Contract ended']);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('status_updated_by')->constrained('users');
            $table->foreignId('property_manager_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_details');
    }
};
