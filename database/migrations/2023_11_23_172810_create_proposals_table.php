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
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_id')->constrained('tenders');
            $table->decimal('amount', 15, 3);
            $table->foreignId('submitted_by')->constrained('vendors');
            $table->date('submitted_on');
            $table->string('document',100);
            $table->string('status')->nullable();
            $table->string('remarks')->nullable();
            $table->foreignId('status_updated_by')->nullable()->constrained('users');
            $table->date('status_updated_on')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
