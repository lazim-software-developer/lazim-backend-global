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
        Schema::table('vendor_employee', function (Blueprint $table) {
            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users');

            $table
                ->foreign('vendor_id')
                ->references('id')
                ->on('vendors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_employee', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['vendor_id']);
        });
    }
};
