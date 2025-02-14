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
        Schema::table('flats', callback: function (Blueprint $table) {
            $table->string('makhani_number')->nullable();
            $table->string('dewa_number')->nullable();
            $table->string('etisalat/du_number')->nullable();
            $table->string('btu/ac_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flats', function (Blueprint $table) {
            $table->dropColumn('makhani_number');
            $table->dropColumn('dewa_number');
            $table->dropColumn('etisalat/du_number');
            $table->dropColumn('btu/ac_number');
        });
    }
};
