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
        Schema::create('apartment_owners', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('owner_number', 50);
            $table->string('email', 100);
            $table->string('name', 100);
            $table->string('mobile', 15);
            $table->string('passport', 50)->nullable();
            $table->string('emirates_id', 50)->nullable();
            $table->string('trade_license', 50)->nullable();
            $table->timestamps();
        });
       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartment_owners');
    }
};
