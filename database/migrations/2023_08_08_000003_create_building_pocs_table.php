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
        Schema::create('building_pocs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('building_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->string('role_name', 50);
            $table->string('escalation_level', 50);
            $table->boolean('active')->nullable();
            $table->boolean('emergency_contact')->nullable();
            $table->timestamps();
            $table->foreign('owner_association_id')->references('id')->on('owner_associations');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('building_pocs');
    }
};
