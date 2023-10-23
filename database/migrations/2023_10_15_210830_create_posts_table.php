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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->longText('content');
            $table->enum('status', ['published', 'draft', 'archived'])->default('draft');
            $table->dateTime('scheduled_at')->nullable();
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_announcement')->default(false);
    
            $table->foreign('building_id')->references('id')->on('buildings');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
