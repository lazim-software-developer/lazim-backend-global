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
            $table->string('agent_email')->nullable();
            $table->string('agent_phone')->nullable();
            $table->string('title_deed')->nullable();
            $table->string('poa_document')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('noc_contacts', function (Blueprint $table) {
            $table->dropColumn('agent_email');
            $table->dropColumn('agent_phone');
            $table->dropColumn('title_deed');
            $table->dropColumn('poa_document');
        });
    }
};
