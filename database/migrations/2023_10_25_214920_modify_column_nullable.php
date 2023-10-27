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
        Schema::table('documents', function (Blueprint $table) {
            $table->string('url')->nullable()->change();
            $table->string('expiry_date')->nullable()->change();
            $table->string('accepted_by')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('url')->nullable(false)->change();
            $table->string('expiry_date')->nullable(false)->change();
            $table->string('accepted_by')->nullable(false)->change();
        });
    }
};
