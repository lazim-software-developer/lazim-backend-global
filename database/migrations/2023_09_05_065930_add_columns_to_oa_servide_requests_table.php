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
        Schema::table('oa_service_requests', function (Blueprint $table) {
            $table->string('property_name')->nullable();
            $table->string('service_period')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oa_service_requests', function (Blueprint $table) {
            $table->dropColumn('property_name');
            $table->dropColumn('service_period');
        });
    }
};
