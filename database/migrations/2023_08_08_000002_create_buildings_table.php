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
        Schema::create('buildings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50);
            $table->string('unit_number', 50)->unique();
            $table->longText('address_line1');
            $table->longText('address_line2')->nullable();
            $table->string('area', 50);
            $table->unsignedBigInteger('city_id');
            $table->string('lat', 50)->nullable();
            $table->string('lng', 50)->nullable();
            $table->longText('description')->nullable();
            $table->integer('floors');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
