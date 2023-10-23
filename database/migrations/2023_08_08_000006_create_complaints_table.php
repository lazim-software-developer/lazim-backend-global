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
        Schema::create('complaints', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('complaintable_type');
            $table->unsignedBigInteger('complaintable_id');
            $table->unsignedBigInteger('building_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->string('category', 50);
            $table->dateTime('open_time')->nullable();
            $table->dateTime('close_time')->nullable();
            $table->json('photo')->nullable();
            $table->json('remarks')->nullable();
            $table->string('status', 50);
            $table->index('complaintable_type');
            $table->index('complaintable_id');
            $table->foreign('owner_association_id')->references('id')->on('owner_associations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
