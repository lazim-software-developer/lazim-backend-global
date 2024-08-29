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
        Schema::table('sale_nocs', function (Blueprint $table) {
            $table->string('signing_authority_email')->nullable()->after('verified_by');
            $table->string('signing_authority_phone')->nullable()->after('signing_authority_email');
            $table->string('signing_authority_name')->nullable()->after('signing_authority_phone');
            $table->string('submit_status')->nullable()->after('signing_authority_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_nocs', function (Blueprint $table) {
            $table->dropColumn('signing_authority_email');
            $table->dropColumn('signing_authority_phone');
            $table->dropColumn('signing_authority_name');
            $table->dropColumn('submit_status');
        });
    }
};
