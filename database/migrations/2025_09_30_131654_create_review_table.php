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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('oa_id');
            $table->unsignedBigInteger('flat_id');
            $table->tinyInteger('type')->comment('1 = feedback');
            $table->longText('comment')->nullable();
            $table->tinyInteger('feedback')->comment('1 = good, 2 = average, 3 = bad');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'oa_id', 'flat_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
