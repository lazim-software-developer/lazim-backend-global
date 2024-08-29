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
            $table->string('remarks')->nullable()->after('emirates_id');
            $table->string('status')->nullable()->after('remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('residential_forms', function (Blueprint $table) {
            $table->dropColumn(['remarks']);
            $table->dropColumn(['status']);
        });
    }
};
