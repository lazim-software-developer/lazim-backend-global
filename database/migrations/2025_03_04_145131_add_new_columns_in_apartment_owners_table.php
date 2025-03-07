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
        if (!Schema::hasColumn('apartment_owners', 'building_id')) {
            Schema::table('apartment_owners', function (Blueprint $table) {
                $table->unsignedBigInteger('building_id')->nullable();
                $table->string('resource')->default('Mollak');
                $table->string('owner_status')->nullable();
                $table->string('primary_owner_mobile')->nullable();
                $table->string('primary_owner_email')->nullable();
                $table->softDeletes();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->foreign('building_id')->references('id')->on('buildings');
                $table->foreign('created_by')->references('id')->on('users');
                $table->foreign('updated_by')->references('id')->on('users');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apartment_owners', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
            $table->dropColumn('building_id');
            $table->dropColumn('owner_status');
            $table->dropColumn('resource');
            $table->dropColumn('primary_owner_mobile');
            $table->dropColumn('primary_owner_email');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
    }
};
