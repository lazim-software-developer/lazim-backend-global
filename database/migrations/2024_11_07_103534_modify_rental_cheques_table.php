<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE rental_cheques
         MODIFY COLUMN cheque_status ENUM('Cancelled', 'Bounced', 'Paid') NULL");
        DB::statement("ALTER TABLE rental_cheques
        MODIFY COLUMN mode_payment ENUM('Online', 'Cheque', 'Cash') DEFAULT 'Cheque'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE rental_cheques
        MODIFY COLUMN cheque_status ENUM('Cancelled', 'Bounced') NOT NULL");
        DB::statement("ALTER TABLE rental_cheques
        MODIFY COLUMN mode_payment ENUM('Online', 'Cheque', 'Cash') DEFAULT NULL");
    }
};
