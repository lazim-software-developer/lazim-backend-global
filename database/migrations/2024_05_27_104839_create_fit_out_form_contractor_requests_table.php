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
        Schema::create('fit_out_form_contractor_requests', function (Blueprint $table) {
            $table->id();
            $table->string('work_type');
            $table->string('work_name');
            $table->unsignedBigInteger('fit_out_form_id');
            $table->string('status');
            $table->foreign('fit_out_form_id')->references('id')->on('fit_out_forms');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fit_out_form_contractor_requests');
    }
};
