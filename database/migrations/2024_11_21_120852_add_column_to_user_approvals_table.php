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
            $table->date('emirates_document_expiry_date')->after('emirates_document')->nullable();
            $table->date('passport_expiry_date')->after('passport')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_approvals', function (Blueprint $table) {
            $table->dropColumn('emirates_document_expiry_date');
            $table->dropColumn('passport_expiry_date');
        });
    }
};
