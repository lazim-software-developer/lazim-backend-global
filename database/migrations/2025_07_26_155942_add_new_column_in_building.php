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
        Schema::table('buildings', function (Blueprint $table) {
            $table->json('common_area_details')->nullable();
        });
        Schema::table('apartment_owners', function (Blueprint $table) {
            $table->string('participant_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buildings', function (Blueprint $table) {
            if (Schema::hasColumn('buildings', 'common_area_details')) {
                $table->dropColumn('common_area_details');
            }
        });
        Schema::table('apartment_owners', function (Blueprint $table) {
            if (Schema::hasColumn('apartment_owners', 'participant_type')) {
                $table->dropColumn('participant_type');
            }
        });
    }
};
