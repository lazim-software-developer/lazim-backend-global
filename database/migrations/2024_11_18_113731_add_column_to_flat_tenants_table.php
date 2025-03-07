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
        Schema::table('flat_tenants', function (Blueprint $table) {
            $table->boolean('residing_in_same_flat')->default(false)->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flat_tenants', function (Blueprint $table) {
            $table->dropColumn('residing_in_same_flat');
        });
    }
};
