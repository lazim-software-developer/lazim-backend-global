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
        Schema::create('owner_association_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->date('from')->nullable();
            $table->date('to')->nullable();
            $table->boolean('active')->default(true);

            $table->foreign('owner_association_id')->references('id')->on('owner_association');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_association_user');
    }
};
