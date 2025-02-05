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
        DB::statement("ALTER TABLE buildings MODIFY COLUMN building_type ENUM('residential', 'commercial', 'residential/commercial') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE buildings MODIFY COLUMN building_type ENUM('residential', 'commercial') NULL");
    }
};
