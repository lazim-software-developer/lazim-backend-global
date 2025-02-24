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
            $table->decimal('invoice_amount', 15, 4)->nullable();
            $table->decimal('amount_paid', 15, 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oam_invoices', function (Blueprint $table) {
            $table->dropColumn('invoice_amount');
            $table->dropColumn('amount_paid');
        });
    }
};
