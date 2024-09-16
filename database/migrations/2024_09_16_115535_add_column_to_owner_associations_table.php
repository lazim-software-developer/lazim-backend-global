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
        Schema::table('owner_associations', function (Blueprint $table) {
            $table->string('trn_number')->nullable()->change();
            $table->unsignedBigInteger('mollak_id')->nullable()->change();
            $table->string('emirates_id')->nullable()->after('mollak_id');
            $table->string('trade_license_number')->nullable()->after('emirates_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owner_associations', function (Blueprint $table) {
            $table->string('trn_number')->unique()->change();
            $table->unsignedBigInteger('mollak_id')->unique()->change();
            $table->dropColumn(['emirates_id']);
            $table->dropColumn(['trade_license_number']);
        });
    }
};
