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
        if (!Schema::hasColumn('flats', 'resource')) {
            Schema::table('flats', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->default(2);
                $table->unsignedBigInteger('updated_by')->default(2);
                $table->foreign('created_by')->references('id')->on('users');
                $table->foreign('updated_by')->references('id')->on('users');
                $table->string('resource')->default('Mollak');
                $table->tinyInteger('status')->default(0)->comment('0: Inactive, 1: Active');
                $table->softDeletes()->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flats', function (Blueprint $table) {
            $table->dropColumn('resource');
            $table->dropColumn('status');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
            $table->dropSoftDeletes();
        });
    }
};
