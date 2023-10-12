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
        Schema::create('flat_tenants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('flat_id');
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('building_id')->nullable();
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->boolean('primary')->nullable()->comment("Is this person head of the family");
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->boolean('active')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_associations');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flat_tenants');
    }
};
