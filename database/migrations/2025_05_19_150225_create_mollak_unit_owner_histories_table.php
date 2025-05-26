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
        Schema::create('mollak_unit_owner_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('flat_id');
            $table->string('owner_number');
            $table->string('email');
            $table->string('mobile');
            $table->foreignId('owner_association_id')->constrained('owner_associations')->onDelete('cascade');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mollak_unit_owner_histories');
    }
};
