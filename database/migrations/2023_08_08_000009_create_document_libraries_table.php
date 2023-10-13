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
        Schema::create('document_libraries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50);
            $table->longText('url');
            $table->string('type', 50);
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
        Schema::dropIfExists('document_libraries');
    }
};
