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
        Schema::create('flat_domestic_helps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('flat_id');
            $table->string('first_name', 50);
            $table->string('last_name', 50)->nullable();
            $table->unsignedBigInteger('building_id')->nullable();
            $table->string('phone', 10)->unique();
            $table->json('profile_photo')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->string('role_name', 50);
            $table->boolean('active');
            $table->foreign('building_id')->references('id')->on('buildings');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flat_domestic_helps');
    }
};
