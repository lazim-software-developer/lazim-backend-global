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
        Schema::create('access_cards', function (Blueprint $table) {
            $table->id();
            $table->string('mobile', 20);
            $table->string('email', 20);
            $table->string('card_type');
            $table->string('reason')->nullable();
            $table->json('parking_details')->nullable();
            $table->string('passport', 100)->nullable();
            $table->string('tenancy', 100)->nullable();
            $table->string('vehicle_registration', 100)->nullable();
            $table->unsignedBigInteger('flat_id');
            $table->unsignedBigInteger('user_id');

            $table->foreign('flat_id')->references('id')->on('flats');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_cards');
    }
};
