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
        if (!Schema::hasColumn('mollak_tenants', 'resource')) {
            Schema::table('mollak_tenants', function (Blueprint $table) {
                $table->string('resource')->nullable();
            });
        }
        if (!Schema::hasColumn('mollak_tenants', 'deleted_at')) {
            Schema::table('mollak_tenants', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mollak_tenants', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('resource');
        });
    }
};
