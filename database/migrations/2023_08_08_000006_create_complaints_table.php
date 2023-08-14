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
            $table->unsignedBigInteger('user_id');
           // $table->string('complaint_type', 50);
            $table->string('category', 50);
            $table->dateTime('open_time');
            $table->dateTime('close_time');
            $table->json('photo')->nullable();
            $table->json('remarks');
            $table->string('status', 50);

            $table->index('complaintable_type');
            $table->index('complaintable_id');

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
