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
        Schema::create('documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('document_library_id');
            $table->unsignedBigInteger('building_id');
            $table->longText('url');
            $table->string('status', 50);
            $table->json('comments');
            $table->date('expiry_date');
            $table->unsignedBigInteger('accepted_by');
            $table->unsignedBigInteger('documentable_id');
            $table->string('documentable_type');

            $table->index('documentable_id');
            $table->index('documentable_type');

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
