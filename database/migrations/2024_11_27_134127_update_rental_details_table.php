<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE rental_details
            MODIFY COLUMN number_of_cheques ENUM('1', '2', '3', '4','6', '12')");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE rental_details
            MODIFY COLUMN number_of_cheques ENUM('1', '2', '3', '4','6')");
    }
};
