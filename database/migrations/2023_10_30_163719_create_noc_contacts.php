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
        Schema::create('noc_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('first_name',100);
            $table->string('last_name',100)->nullable();
            $table->string('email', 80);
            $table->string('mobile',20);
            $table->string('emirates_id',20)->nullable();
            $table->string('passport_number',20)->nullable();
            $table->string('visa_number',20)->nullable();
            $table->string('emirates_document_url');
            $table->string('visa_document_url');
            $table->string('passport_document_url');

            $table->unsignedBigInteger('noc_form_id');
            $table->foreign('noc_form_id')->references('id')->on('noc_forms');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('noc_contacts');
    }
};
