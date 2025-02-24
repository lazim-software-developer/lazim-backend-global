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
        Schema::table('vendor_escalation_matrix', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('vendor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_escalation_matrix', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
};
