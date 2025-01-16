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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('company_name');
            $table->string('email');
            $table->string('phone');
            $table->string('address')->nullable();
            $table->string('state')->nullable();
            $table->bigInteger('number_of_communities');
            $table->bigInteger('number_of_units');
            $table->longText('message')->nullable();
            $table->json('features')->nullable();
            $table->json('onboarding_assistance')->nullable();
            $table->json('support')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
