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
        Schema::create('tendor_vendors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tendor_id');
            $table->unsignedBigInteger('vendor_id');
            $table->timestamps();

            $table->foreign('tendor_id')->references('id')->on('tendors');
            $table->foreign('vendor_id')->references('id')->on('vendors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tendor_vendors');
    }
};
