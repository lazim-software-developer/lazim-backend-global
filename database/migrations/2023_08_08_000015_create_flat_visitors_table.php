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
        Schema::create('flat_visitors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('flat_id');
            $table->unsignedBigInteger('building_id')->nullable();
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->string('name', 50);
            $table->string('phone', 10);
            $table->string('type', 50);
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('verification_code');
            $table->unsignedBigInteger('initiated_by');
            $table->unsignedBigInteger('approved_by');
            $table->json('remarks');
            $table->integer('number_of_visitors');
            $table->foreign('owner_association_id')->references('id')->on('owner_associations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flat_visitors');
    }
};
