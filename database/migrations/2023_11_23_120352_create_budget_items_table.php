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
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets');
            $table->foreignId('service_id')->constrained('services');
            $table->decimal('budget_excl_vat', 15, 3);
            $table->decimal('vat_rate', 5, 3);
            $table->decimal('vat_amount', 15, 3);
            $table->decimal('total', 15, 3);
            $table->decimal('rate', 15, 3)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_items');
    }
};
