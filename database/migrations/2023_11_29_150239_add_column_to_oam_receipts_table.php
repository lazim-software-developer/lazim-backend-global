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
        Schema::table('oam_receipts', function (Blueprint $table) {
            $table->string('receipt_period')->nullable()->after('receipt_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oam_receipts', function (Blueprint $table) {
            $table->dropColumn('receipt_period');
        });
    }
};
