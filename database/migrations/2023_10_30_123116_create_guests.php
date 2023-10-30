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
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->integer('passport_number');
            $table->date('visa_validity_date');
            $table->integer('stay_duration');
            $table->date('expiry_date');
            $table->string('dtmc_license_url');
            $table->boolean('access_card_holder')->default(false);
            $table->boolean('original_passport')->default(false);
            $table->boolean('guest_registration')->default(false);
            $table->unsignedBigInteger('flat_visitor_id');
            $table->foreign('flat_visitor_id')->references('id')->on('flat_visitors');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
