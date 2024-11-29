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
        Schema::table('rental_cheques', function (Blueprint $table) {
            $table->boolean('payment_link_requested')->default(false)->after('payment_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rental_cheques', function (Blueprint $table) {
            $table->dropColumn('payment_link_requested');
        });
    }
};
