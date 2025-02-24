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
        Schema::create('offer_promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('image');
            $table->longText('description');
            $table->date('start_date');
            $table->date('end_date');
            $table->longText('link')->nullable();
            $table->foreignId('building_id')->constrained('buildings');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_promotions');
    }
};
