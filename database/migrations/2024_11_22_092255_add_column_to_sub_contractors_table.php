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
        Schema::table('sub_contractors', function (Blueprint $table) {
            $table->date('trade_licence_expiry_date')->nullable()->after('trade_licence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_contractors', function (Blueprint $table) {
            $table->dropColumn('trade_licence_expiry_date');
        });
    }
};
