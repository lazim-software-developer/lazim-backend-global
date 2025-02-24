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
            $table->string('title_deed')->nullable();
            $table->string('passport')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('access_cards', function (Blueprint $table) {
            $table->dropColumn('title_deed');
            $table->dropColumn('passport');
        });
    }
};
