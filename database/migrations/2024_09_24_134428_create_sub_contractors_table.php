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
        Schema::create('sub_contractors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('company_name')->nullable();
            $table->string('trn_no');
            $table->string('service_provided');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('trade_licence');
            $table->string('contract_paper');
            $table->string('agreement_letter');
            $table->string('additional_doc')->nullable();
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_contractors');
    }
};
