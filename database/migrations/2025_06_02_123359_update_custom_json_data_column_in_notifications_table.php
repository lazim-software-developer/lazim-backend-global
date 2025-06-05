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
        DB::table('notifications')
            ->whereNull('custom_json_data')
            ->update(['custom_json_data' => json_encode(['owner_association_id' => 1])]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('notifications')
            ->whereNull('custom_json_data')
            ->update(['custom_json_data' => null]);
    }
};
