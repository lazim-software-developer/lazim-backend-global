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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name', 50);
            $table->string('last_name', 50)->nullable();
            $table->string('email', 50)->unique();
            $table->string('phone', 10)->unique()->nullable();
            $table->string('password');
            $table->boolean('email_verified')->nullable();
            $table->boolean('phone_verified')->nullable();
            $table->boolean('active')->nullable();
            $table->string('lazim_id', 50)->unique()->nullable();
            $table->unsignedBigInteger('role_id')->nullable();

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
