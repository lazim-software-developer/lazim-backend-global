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
        Schema::table('mollak_tenants', function (Blueprint $table) {
            $table->string('passport')->nullable()->after('emirates_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mollak_tenants', function (Blueprint $table) {
            $table->dropColumn('passport');
        });
    }
};
