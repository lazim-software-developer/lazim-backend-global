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
        Schema::table('noc_contacts', function (Blueprint $table) {
            $table->string('emirates_document_url')->nullable()->change();
            $table->string('visa_document_url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('noc_contacts', function (Blueprint $table) {
            $table->string('emirates_document_url')->nullable(false)->change();
            $table->string('visa_document_url')->nullable(false)->change();
        });
    }
};
