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
        Schema::table('user_approvals', function (Blueprint $table) {
            $table->longText('trade_license')->nullable();
        });

        Schema::table('user_approval_audits', function (Blueprint $table) {
            $table->longText('trade_license')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_approvals', function (Blueprint $table) {
            $table->dropColumn('trade_license');
        });

        Schema::table('user_approval_audits', function (Blueprint $table) {
            $table->dropColumn('trade_license');
        });
    }
};
