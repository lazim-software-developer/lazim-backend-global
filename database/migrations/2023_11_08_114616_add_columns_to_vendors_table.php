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
        Schema::table('vendors', function (Blueprint $table) {
            $table->longText('address_line_1')->after('owner_association_id');
            $table->longText('address_line_2')->nullable()->after('address_line_1');
            $table->string('landline_number',50)->after('address_line_2');
            $table->string('website',100)->nullable()->after('landline_number');
            $table->string('fax',100)->nullable()->after('website');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('address_line_1');
            $table->dropColumn('address_line_2');
            $table->dropColumn('landline_number');
            $table->dropColumn('website');
            $table->dropColumn('fax');
        });
    }
};
