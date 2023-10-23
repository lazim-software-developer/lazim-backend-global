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
        Schema::create('flats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('number');
            $table->integer('floor');
            $table->unsignedBigInteger('building_id')->nullable();
            $table->unsignedBigInteger('owner_association_id')->nullable();

            $table->string('description', 50)->nullable();
            $table->timestamps();
            $table->foreign('owner_association_id')->references('id')->on('owner_associations');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flats');
    }
};
