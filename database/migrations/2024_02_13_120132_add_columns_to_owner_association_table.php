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
            $table->string('bank_account_number')->nullable();
            $table->longText('trade_license')->nullable();
            $table->longText('dubai_chamber_document')->nullable();
            $table->longText('memorandum_of_association')->nullable();
            $table->longText('trn_certificate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owner_associations', function (Blueprint $table) {
            $table->dropColumn('bank_account_number');
            $table->dropColumn('trade_license');
            $table->dropColumn('dubai_chamber_document');
            $table->dropColumn('memorandum_of_association');
            $table->dropColumn('trn_certificate');
        });
    }
};
