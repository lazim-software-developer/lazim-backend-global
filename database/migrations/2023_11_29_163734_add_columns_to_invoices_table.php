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
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('opening_balance', 15, 4)->after('vendor_id')->nullable();
            $table->decimal('payment', 15, 4)->after('opening_balance')->nullable();
            $table->decimal('balance', 15, 4)->after('payment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('opening_balance');
            $table->dropColumn('payment');
            $table->dropColumn('balance');
        });
    }
};
