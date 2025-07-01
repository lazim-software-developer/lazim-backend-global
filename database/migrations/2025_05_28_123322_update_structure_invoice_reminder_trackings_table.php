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
        Schema::dropIfExists('invoice_reminder_trackings');
        Schema::create('invoice_reminder_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('oam_invoices')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('invoice_number');
            $table->decimal('invoice_amount', 10, 2);
            $table->date('invoice_actual_date')->nullable();
            $table->string('user_email');
            $table->foreignId('building_id')->constrained('buildings')->onDelete('cascade');
            $table->foreignId('flat_id')->constrained('flats')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_reminder_trackings');
    }
};
