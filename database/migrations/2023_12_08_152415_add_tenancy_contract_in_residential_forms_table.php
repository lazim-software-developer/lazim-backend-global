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
        Schema::table('residential_forms', function (Blueprint $table) {
           $table->string('tenancy_contract')->nullable();
           $table->json('rejected_fields')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('residential_forms', function (Blueprint $table) {
            $table->dropColumn('tenancy_contract');
            $table->dropColumn('rejected_fields');
        });
    }
};
