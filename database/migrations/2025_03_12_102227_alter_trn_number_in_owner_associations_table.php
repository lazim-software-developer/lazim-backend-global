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
            // Drop the unique constraint
            $table->dropUnique(['trn_number']);
            $table->dropUnique(['email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owner_associations', function (Blueprint $table) {
            // Re-add the unique constraint if you need to roll back
            $table->unique('trn_number');
            $table->unique('email');
        });
    }
};
