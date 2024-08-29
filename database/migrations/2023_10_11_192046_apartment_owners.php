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
            $table->string('owner_number', 100);
            $table->string('email', 100)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('mobile', 100)->nullable();
            $table->string('passport', 100)->nullable();
            $table->string('emirates_id', 100)->nullable();
            $table->string('trade_license', 100)->nullable();
            $table->timestamps();
        });
       
    }

    /**
     * Reverse the migrations.pserviceperiodre
     */
    public function down(): void
    {
        Schema::dropIfExists('apartment_owners');
    }
};
