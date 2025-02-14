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
        Schema::create('property_manager_flats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_association_id');
            $table->unsignedBigInteger('flat_id');
            $table->boolean('active')->default(true);
            
            $table->foreign('owner_association_id')->references('id')->on('owner_associations');
            $table->foreign('flat_id')->references('id')->on('flats');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_manager_flats');
    }
};
