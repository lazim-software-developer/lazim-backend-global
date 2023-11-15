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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->morphs('budgetable'); // This will create `budgetable_type` and `budgetable_id`
            $table->decimal('budget_excl_vat', 15, 3);
            $table->decimal('vat_rate', 5, 3);
            $table->decimal('vat_amount', 15, 3);
            $table->decimal('total', 15, 3);
            $table->decimal('rate', 15, 3);
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->string('budget_period');
            $table->date('budget_from');
            $table->date('budget_to');
            $table->timestamps();

            $table->foreign('building_id')->references('id')->on('buildings');
            $table->foreign('owner_association_id')->references('id')->on('owner_associations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
