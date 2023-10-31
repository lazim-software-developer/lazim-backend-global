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
            $table->string('handover_acceptance', 100)->nullable();
            $table->string('receipt_charges', 100)->nullable();
            $table->string('contract', 100)->nullable();
            $table->string('title_deed', 100)->nullable();
            $table->string('passport', 100)->nullable();
            $table->string('dewa', 100)->nullable();
            $table->string('cooling_registration', 100)->nullable();
            $table->string('gas_registration', 100)->nullable();
            $table->string('vehicle_registration', 100)->nullable();
            $table->string('movers_license', 100)->nullable();
            $table->string('movers_liability', 100)->nullable();
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
