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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->double('amount',10,2);
            $table->date('month');
            $table->enum('type',['BTU','DEWA','Telecommunication', 'lpg']);
            $table->foreignId('flat_id')->constrained('flats');
            $table->date('due_date');
            $table->date('uploaded_on')->default(now());
            $table->enum('status',['Pending', 'Paid', 'Overdue']);
            $table->foreignId('uploaded_by')->constrained('users');
            $table->foreignId('status_updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
