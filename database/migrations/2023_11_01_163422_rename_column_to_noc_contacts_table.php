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
            $table->renameColumn('noc_form_id', 'noc_forms_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('noc_contacts', function (Blueprint $table) {
            $table->renameColumn('noc_forms_id', 'noc_form_id');
        });
    }
};
