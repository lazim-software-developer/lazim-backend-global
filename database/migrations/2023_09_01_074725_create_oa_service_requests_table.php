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
        Schema::create('oa_service_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_parameter_id');
            $table->string('property_group');
            $table->date('from_date');
            $table->date('to_date');
            $table->string('status');
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oa__service__requests');
    }
};
