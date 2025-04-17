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
        if (!Schema::hasColumn('buildings', 'deleted_at')) {
            Schema::table('buildings', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
        if (!Schema::hasColumn('buildings', 'status')) {
            Schema::table('buildings', function (Blueprint $table) {
                $table->string('status')->nullable()->comment('Status of the building (e.g. 0: Inactive, 1: Active	)');
            });
        }
        if (!Schema::hasColumn('buildings', 'created_by')) {
            Schema::table('buildings', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }
        if (!Schema::hasColumn('buildings', 'updated_by')) {
            Schema::table('buildings', function (Blueprint $table) {
                $table->unsignedBigInteger('updated_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }
        if (!Schema::hasColumn('buildings', 'resource')) {
            Schema::table('buildings', function (Blueprint $table) {
                $table->string('resource')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('status');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
            $table->dropColumn('resource');
        });
    }
};
