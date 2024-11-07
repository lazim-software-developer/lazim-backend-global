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
        Schema::create('rental_cheques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_detail_id')->constrained('rental_details');
            $table->string('cheque_number');
            $table->double('amount', 10, 2);
            $table->date('due_date');
            $table->enum('status', ['Overdue', 'Paid', 'Upcoming'])->default('Upcoming');
            $table->foreignId('status_updated_by')->constrained('users')->nullable();
            $table->enum('mode_payment', ['Online', 'Cheque', 'Cash']);
            $table->enum('cheque_status', ['Cancelled', 'Bounced']);
            $table->string('payment_link')->nullable();
            $table->json('comments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_cheques');
    }
};
