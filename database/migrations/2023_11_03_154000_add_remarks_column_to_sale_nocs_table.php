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
        Schema::table('sale_nocs', function (Blueprint $table) {
            $table->string('status', 100)->nullable()->change();
            $table->string('remarks')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_nocs', function (Blueprint $table) {
            $table->dropColumn(['status']);
            $table->dropColumn(['remarks']);
        });
    }
};
