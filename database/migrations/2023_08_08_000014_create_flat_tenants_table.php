<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('flat_tenants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('flat_id');
            $table->unsignedBigInteger('tenant_id');
            $table->boolean('primary');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->boolean('active');

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
