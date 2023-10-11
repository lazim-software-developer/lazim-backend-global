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
        Schema::create('services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50);
            $table->boolean('custom')->nullable();
            $table->boolean('active')->nullable();
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_associations');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
