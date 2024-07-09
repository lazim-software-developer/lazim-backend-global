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
        Schema::create('auth_credentials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_association_id');
            $table->string('client_id')->unique();
            $table->string('api_key');
            $table->string('module');
            $table->timestamps();

            $table->foreign('owner_association_id')->references('id')->on('owner_associations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_credentials');
    }
};
