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
        Schema::table('sale_nocs', function (Blueprint $table) {
            $table->date('service_charge_paid_till')->nullable()->change();
            $table->string('cooling_receipt', 100)->nullable()->change();
            $table->string('cooling_soa', 100)->nullable()->change();
            $table->string('cooling_clearance', 100)->nullable()->change();
            $table->string('payment_receipt', 100)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_nocs', function (Blueprint $table) {
            $table->date('service_charge_paid_till')->nullable(false)->change();
            $table->string('cooling_receipt', 100)->nullable(false)->change();
            $table->string('cooling_soa', 100)->nullable(false)->change();
            $table->string('cooling_clearance', 100)->nullable(false)->change();
            $table->string('payment_receipt', 100)->nullable(false)->change();
        });
    }
};
