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
        Schema::create('owner_association_vendor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->date('from')->nullable();
            $table->date('to')->nullable();
            $table->boolean('active')->default(true);

            $table->foreign('owner_association_id')->references('id')->on('owner_associations');
            $table->foreign('vendor_id')->references('id')->on('vendors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_association_vendor');
    }
};
