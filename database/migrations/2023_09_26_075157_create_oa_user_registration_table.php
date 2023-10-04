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
        Schema::create('oa_user_registration', function (Blueprint $table) {
            $table->id();
            $table->integer('oa_id');
            $table->string('name');
            $table->unsignedBigInteger('building_id')->nullable();
            $table->string('phone', 20)->unique();
            $table->string('email', 30)->unique();
            $table->string('trn', 50)->unique();
            $table->longText('address');
            $table->boolean('verified')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->foreign('verified_by')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('building_id')->references('id')->on('buildings')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oa_user_registration');
    }
};
