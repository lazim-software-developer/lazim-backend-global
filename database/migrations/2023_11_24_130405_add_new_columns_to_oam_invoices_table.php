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
        Schema::table('oam_invoices', function (Blueprint $table) {
            $table->string('type')->nullable();
            $table->string('invoice_quarter')->nullable();
            $table->string('invoice_period')->nullable();
            $table->string('budget_period')->nullable();
            $table->string('service_charge_group_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oam_invoices', function (Blueprint $table) {
            $table->dropColumn(['type', 'invoice_quarter', 'invoice_period', 'budget_period', 'service_charge_group_id']);
        });
    }
};
