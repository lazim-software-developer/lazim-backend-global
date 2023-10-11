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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name', 50);
            $table->string('last_name', 50)->nullable();
            $table->string('email', 50)->unique();
            $table->string('phone', 20)->unique()->nullable();
            $table->string('password')->nullable();
            $table->boolean('email_verified')->nullable();
            $table->boolean('phone_verified')->nullable();
            $table->boolean('active')->nullable();
            $table->string('lazim_id', 50)->unique()->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_associations');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
