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
            $table->unsignedBigInteger('building_id');
            $table->string('name', 50);
            $table->string('phone', 10)->unique();
            $table->string('type', 50);
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('verification_code')->unique();
            $table->unsignedBigInteger('initiated_by');
            $table->unsignedBigInteger('approved_by');
            $table->json('remarks');
            $table->integer('number_of_visitors');

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
