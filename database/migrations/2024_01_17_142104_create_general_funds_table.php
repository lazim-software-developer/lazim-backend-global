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
        Schema::create('general_funds', function (Blueprint $table) {
            $table->id();
            $table->date('statement_date');
            $table->date('date');
            $table->string('description');
            $table->decimal('debited_amount',15,4);
            $table->decimal('credited_amount',15,4);
            $table->string('type')->nullable();
            $table->unsignedBigInteger('building_id');

            $table->foreign('building_id')->references('id')->on('buildings');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_funds');
    }
};
