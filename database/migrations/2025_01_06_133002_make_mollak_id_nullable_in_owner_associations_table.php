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
        Schema::table('owner_associations', function (Blueprint $table) {
            $table->dropUnique('owner_associations_mollak_id_unique');
            $table->string('mollak_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owner_associations', function (Blueprint $table) {
            // Re-add the unique constraint
            $table->unique('mollak_id');

            // Change the column back to non-nullable
            $table->string('mollak_id')->nullable(false)->change();
        });
    }
};
