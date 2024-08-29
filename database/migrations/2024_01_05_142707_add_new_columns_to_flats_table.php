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
        Schema::table('flats', function (Blueprint $table) {
            $table->string('suit_area')->nullable();
            $table->string('actual_area')->nullable();
            $table->string('balcony_area')->nullable();
            $table->string('applicable_area')->nullable();
            $table->string('virtual_account_number')->nullable();
            $table->integer('parking_count')->nullable();
            $table->integer('plot_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flats', function (Blueprint $table) {
            $table->dropColumn('suit_area');
            $table->dropColumn('actual_area');
            $table->dropColumn('balcony_area');
            $table->dropColumn('applicable_area');
            $table->dropColumn('virtual_account_number');
            $table->dropColumn('parking_count');
            $table->dropColumn('plot_number');
        });
    }
};
