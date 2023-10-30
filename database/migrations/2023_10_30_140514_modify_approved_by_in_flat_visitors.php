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
        Schema::table('flat_visitors', function (Blueprint $table) {
            $table->integer('verification_code')->nullable()->change();
            $table->unsignedBigInteger  ('approved_by')->nullable()->change();
            $table->json('remarks')->nullable()->change();
            $table->string('phone',20)->change();
            // $table->integer('number_of_visitors')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flat_visitors', function (Blueprint $table) {
            $table->integer('verification_code')->change();
            $table->unsignedBigInteger  ('approved_by')->change();
            $table->json('remarks')->change();
            $table->string('phone',20)->change();
        });
    }
};
