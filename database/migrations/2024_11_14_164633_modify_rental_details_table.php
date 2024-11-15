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

            DB::statement("ALTER TABLE rental_details
            MODIFY COLUMN status ENUM('Active', 'Contract extended', 'Contract ended')
            DEFAULT 'Active'");

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rental_details', function (Blueprint $table) {
            DB::statement("ALTER TABLE rental_details
            MODIFY COLUMN status ENUM('Active', 'Expired', 'Contract extended', 'Contract ended')");

        });
    }
};
