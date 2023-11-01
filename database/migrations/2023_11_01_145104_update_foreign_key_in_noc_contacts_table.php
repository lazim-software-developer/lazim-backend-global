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
        Schema::table('noc_contacts', function (Blueprint $table) {
            // Drop the old foreign key constraint
            $table->dropForeign(['noc_contacts_noc_form_id_foreign']);

            // Add the new foreign key constraint
            $table->foreign('noc_form_id')->references('id')->on('sale_nocs');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('noc_contacts', function (Blueprint $table) {
            //
        });
    }
};
