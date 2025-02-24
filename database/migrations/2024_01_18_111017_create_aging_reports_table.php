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
        Schema::create('aging_reports', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('flat_id');
            $table->unsignedBigInteger('owner_id');
            $table->decimal('outstanding_balance', 15, 2)->nullable();
            $table->decimal('balance_1', 15, 2)->nullable();
            $table->decimal('balance_2', 15, 2)->nullable();
            $table->decimal('balance_3', 15, 2)->nullable();
            $table->decimal('balance_4', 15, 2)->nullable();
            $table->decimal('over_balance', 15, 2)->nullable();
            $table->bigInteger('year');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aging_reports');
    }
};
