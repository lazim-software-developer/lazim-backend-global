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
        Schema::table('tenders', function (Blueprint $table) {
             // Add the service_id column
             $table->unsignedBigInteger('service_id')->after('owner_association_id')->nullable();
            
             // Add the foreign key constraint
             $table->foreign('service_id')->references('id')->on('services');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenders', function (Blueprint $table) {
            // Remove the foreign key constraint
            $table->dropForeign(['service_id']);
            
            // Remove the service_id column
            $table->dropColumn('service_id');
        });
    }
};
