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
        Schema::table('legal_notice', function (Blueprint $table) {
            $table->string('invoiceNumber');
            $table->date('due_date')->nullable();
            $table->string('case_status')->nullable();
            $table->string('case_number')->nullable();
            $table->string('case_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legal_notice', function (Blueprint $table) {
            $table->dropColumn('invoiceNumber');
            $table->dropColumn('due_date');
            $table->dropColumn('case_status');
            $table->dropColumn('case_number');
            $table->dropColumn('case_type');
        });
    }
};
