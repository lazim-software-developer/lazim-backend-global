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
        Schema::table('family_members', function (Blueprint $table) {
            $table->string('passport_number')->nullable()->change();
            $table->date('passport_expiry_date')->nullable()->change();
            $table->date('emirates_expiry_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('family_members', function (Blueprint $table) {
            $table->string('passport_number')->nullable(false)->change();
            $table->date('passport_expiry_date')->nullable(false)->change();
            $table->date('emirates_expiry_date')->nullable(false)->change();
        });
    }
};
