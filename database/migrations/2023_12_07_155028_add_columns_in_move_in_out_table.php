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
        Schema::table('move_in_out', function (Blueprint $table) {
            $table->string('noc_landlord')->nullable();
            $table->string('cooling_final')->nullable();
            $table->string('gas_final')->nullable();
            $table->string('cooling_clearance')->nullable();
            $table->string('gas_clearance')->nullable();
            $table->string('dewa_final')->nullable();
            $table->string('etisalat_final')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('move_in_out', function (Blueprint $table) {
            $table->dropColumn('noc_landlord');
            $table->dropColumn('cooling_final');
            $table->dropColumn('gas_final');
            $table->dropColumn('cooling_clearance');
            $table->dropColumn('gas_clearance');
            $table->dropColumn('dewa_final');
            $table->dropColumn('etisalat_final');
        });
    }
};
