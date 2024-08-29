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
        Schema::create('cooling_accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('building_id')->constrained('buildings')->onDelete('cascade');
            $table->foreignId('flat_id')->constrained('flats')->onDelete('cascade');
            $table->date('date');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('consumption', 15, 2)->default(0);
            $table->decimal('demand_charge', 15, 2)->default(0);
            $table->decimal('security_deposit', 15, 2)->default(0);
            $table->decimal('billing_charges', 15, 2)->default(0);
            $table->decimal('other_charges', 15, 2)->default(0);
            $table->decimal('receipts', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cooling_accounts');
    }
};
