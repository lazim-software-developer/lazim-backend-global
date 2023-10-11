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
        Schema::table('vendors', function (Blueprint $table) {
            $table->unsignedBigInteger('building_id')->nullable()->change();
            $table->string('status', 50)->nullable()->change();
            $table->json('remarks')->nullable()->change();
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_associations');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->unsignedBigInteger('building_id')->nullable()->change();
            $table->string('status', 50)->nullable()->change();
            $table->json('remarks')->nullable()->change();
            $table->unsignedBigInteger('owner_association_id')->nullable();


        });
    }
};
