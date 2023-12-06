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
        Schema::table('complaints', function (Blueprint $table) {
            $table->integer('priority')->after('owner_association_id')->nullable();
            $table->date('due_date')->after('priority')->nullable();
            $table->unsignedBigInteger('service_id')->after('due_date')->nullable();
            $table->foreign('service_id')->references('id')->on('services');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn('priority');
            $table->dropColumn('due_date');
            $table->dropColumn('service_id');
        });
    }
};
