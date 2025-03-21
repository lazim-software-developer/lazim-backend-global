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
        if (!Schema::hasColumn('owner_associations', 'oa_number')) {
            Schema::table('owner_associations', function (Blueprint $table) {
                $table->string('oa_number')->nullable();
                $table->unsignedBigInteger('created_by')->default(2);
                $table->unsignedBigInteger('updated_by')->default(2);
                $table->foreign('created_by')->references('id')->on('users');
                $table->foreign('updated_by')->references('id')->on('users');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owner_associations', function (Blueprint $table) {
            $table->dropColumn('oa_number');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
    }
};
