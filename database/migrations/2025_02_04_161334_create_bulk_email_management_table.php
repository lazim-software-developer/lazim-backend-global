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
        Schema::create('bulk_email_managements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('email_template_id')->constrained()->onDelete('cascade');
            $table->string('file_path');
            $table->foreignId('owner_association_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'processing', 'failed', 'success'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_attachments');
    }
};
