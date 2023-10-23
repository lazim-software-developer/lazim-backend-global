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
        Schema::create('documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('document_library_id');
            $table->unsignedBigInteger('building_id')->nullable();
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->longText('url');
            $table->string('status', 50);
            $table->json('comments')->nullable();
            $table->date('expiry_date');
            $table->unsignedBigInteger('accepted_by');
            $table->unsignedBigInteger('documentable_id');
            $table->string('documentable_type');
            $table->index('documentable_id');
            $table->index('documentable_type'); $table->foreign('owner_association_id')->references('id')->on('owner_associations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
