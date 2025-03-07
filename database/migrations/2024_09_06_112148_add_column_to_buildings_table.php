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
        Schema::table('buildings', function (Blueprint $table) {
            $table->boolean('show_inhouse_services')->default(false)->change();
            $table->string('mollak_property_id')->nullable()->after('show_inhouse_services');
            $table->string('managed_by')->default('OA')->after('mollak_property_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->dropColumn(['mollak_property_id', 'managed_by']);
            $table->boolean('show_inhouse_services')->default(true)->change();
        });
    }
};
