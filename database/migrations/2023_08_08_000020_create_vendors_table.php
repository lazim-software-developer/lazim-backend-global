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
        Schema::create('vendors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50);
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('building_id')->nullable();
            $table->string('tl_number', 50)->unique();
            $table->date('tl_expiry');
            $table->string('status', 50)->nullable();
            $table->json('remarks')->nullable();
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->timestamps();
            $table->foreign('owner_association_id')->references('id')->on('owner_associations');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
