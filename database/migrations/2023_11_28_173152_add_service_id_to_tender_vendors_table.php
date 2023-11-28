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
        Schema::table('tender_vendors', function (Blueprint $table) {
            $table->string('status')->nullable()->after('service_id');
            $table->string('remarks')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tender_vendors', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('remarks');
        });
    }
};
