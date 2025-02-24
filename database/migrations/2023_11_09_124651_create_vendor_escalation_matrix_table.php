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
        Schema::create('vendor_escalation_matrix', function (Blueprint $table) {
            $table->id();
            $table->string('name',50);
            $table->string('email', 50);
            $table->string('phone', 20);
            $table->string('position',50);
            $table->integer('escalation_level');
            $table->unsignedBigInteger('vendor_id');

            $table->foreign('vendor_id')->references('id')->on('vendors');

            $table->unique(['email', 'vendor_id'], 'unique_email_vendor_id');
            $table->unique(['phone', 'vendor_id'], 'unique_phone_vendor_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_escalation_matrix');
    }
};
