<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->bigIncrements('id');
            //$table->unsignedBigInteger('building_id');
            $table->string('name', 50);
            $table->string('phone', 10)->unique();
            $table->string('email', 50)->unique();
            $table->string('designation', 50);
            $table->string('contactable_type', 50);
            $table->unsignedBigInteger('contactable_id');

            $table->index('contactable_type');
            $table->index('contactable_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
