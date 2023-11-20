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
        Schema::create('asset_maintenance', function (Blueprint $table) {
            $table->id();
            $table->date('maintenance_date');
            $table->json('comment')->nullable();
            $table->json('media')->nullable();
            $table->string('status');
            $table->unsignedBigInteger('technician_asset_id');
            $table->unsignedBigInteger('maintained_by');
            $table->unsignedBigInteger('building_id');

            // Foreign keys
            $table->foreign('technician_asset_id')->references('id')->on('technician_assets');
            $table->foreign('maintained_by')->references('id')->on('users');
            $table->foreign('building_id')->references('id')->on('buildings');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_maintenance');
    }
};
