<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('flat_domestic_helps', function (Blueprint $table) {
            $table
                ->foreign('flat_id')
                ->references('id')
                ->on('flats')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flat_domestic_helps', function (Blueprint $table) {
            $table->dropForeign(['flat_id']);
        });
    }
};
