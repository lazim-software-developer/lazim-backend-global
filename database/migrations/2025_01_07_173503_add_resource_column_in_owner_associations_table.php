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
        if (!Schema::hasColumn('owner_associations', 'resource')) {
            Schema::table('owner_associations', function (Blueprint $table) {
                $table->enum('resource', ['Default', 'Mollak']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owner_associations', function (Blueprint $table) {
            $table->dropColumn('resource');
        });
    }
};
