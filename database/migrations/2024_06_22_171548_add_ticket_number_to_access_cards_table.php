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
        Schema::table('access_cards', function (Blueprint $table) {
            $table->string('ticket_number', 26)->nullable()->after('passport');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('access_cards', function (Blueprint $table) {
            $table->dropColumn('ticket_number');
        });
    }
};
