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
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('passport_number');
            $table->date('passport_expiry_date');
            $table->string('emirates_id');
            $table->date('emirates_expiry_date');
            $table->string('gender');
            $table->string('relation');
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->foreign('owner_association_id')->references('id')->on('owner_associations');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};
