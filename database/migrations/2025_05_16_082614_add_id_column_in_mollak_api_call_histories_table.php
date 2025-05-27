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
        Schema::table('mollak_api_call_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('record_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mollak_api_call_histories', function (Blueprint $table) {
            $table->dropColumn('record_id');
        });
    }
};
