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
        Schema::table('rental_details', function (Blueprint $table) {
            $table->double('admin_charges')->nullable()->after('admin_fee');
            $table->double('brokerage')->nullable()->after('admin_charges');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rental_details', function (Blueprint $table) {
            $table->dropColumn('admin_charges');
            $table->dropColumn('brokerage');
        });
    }
};
