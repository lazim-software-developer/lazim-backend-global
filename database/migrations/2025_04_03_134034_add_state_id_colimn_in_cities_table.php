<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('cities') && !Schema::hasColumn('cities', 'state_id')) {
            Schema::table('cities', function (Blueprint $table) {
                $table->foreignId('state_id')->nullable()->constrained('states')->nullOnDelete();
            });
        }
        $file = file_get_contents(base_path('public/cities.sql'));
        DB::statement($file);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn('state_id');
        });
    }
};
