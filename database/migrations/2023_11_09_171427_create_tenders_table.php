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
        Schema::create('tendors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by');
            $table->date('date');
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('budget_id');
            $table->unsignedBigInteger('owner_association_id');
            $table->date('end_date');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('building_id')->references('id')->on('buildings');
            $table->foreign('budget_id')->references('id')->on('budgets');
            $table->foreign('owner_association_id')->references('id')->on('owner_associations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenders');
    }
};
