<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vendor_escalation_matrix', function (Blueprint $table) {
            $table->string('name')->change();
            $table->string('email')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_escalation_matrix', function (Blueprint $table) {
            $table->string('name',50)->change();
            $table->string('email',50)->change();
        });
    }
};
