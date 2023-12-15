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
        Schema::create('noc_form_signed_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('noc_form_id');
            $table->string('document');
            $table->unsignedBigInteger('uploaded_by'); // Referring to a user ID

            $table->foreign('noc_form_id')->references('id')->on('sale_nocs');
            $table->foreign('uploaded_by')->references('id')->on('users');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('noc_form_signed_documents');
    }
};
