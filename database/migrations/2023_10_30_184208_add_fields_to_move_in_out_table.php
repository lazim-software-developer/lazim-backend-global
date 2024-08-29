<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('move_in_out', function (Blueprint $table) {
            $table->string('handover_acceptance')->nullable();
            $table->string('receipt_charges')->nullable();
            $table->string('contract')->nullable();
            $table->string('title_deed')->nullable();
            $table->string('passport')->nullable();
            $table->string('dewa')->nullable();
            $table->string('cooling_registration')->nullable();
            $table->string('gas_registration')->nullable();
            $table->string('vehicle_registration')->nullable();
            $table->string('movers_license')->nullable();
            $table->string('movers_liability')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('move_in_out', function (Blueprint $table) {
            $table->dropColumn([
                'handover_acceptance',
                'receipt_charges',
                'contract',
                'title_deed',
                'passport',
                'dewa',
                'cooling_registration',
                'gas_registration',
                'vehicle_registration',
                'movers_license',
                'movers_liability'
            ]);
        });
    }
};
