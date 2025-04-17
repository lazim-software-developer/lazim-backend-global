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
        if (!Schema::hasColumn('owner_associations', 'deleted_at')) {
            Schema::table('owner_associations', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
        if (!Schema::hasColumn('owner_associations', 'oa_number')) {
            Schema::table('owner_associations', function (Blueprint $table) {
                $table->string('oa_number')->nullable();
            });
        }
        if (!Schema::hasColumn('owner_associations', 'created_by')) {
            Schema::table('owner_associations', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }
        if (!Schema::hasColumn('owner_associations', 'updated_by')) {
            Schema::table('owner_associations', function (Blueprint $table) {
                $table->unsignedBigInteger('updated_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }
        if (!Schema::hasColumn('owner_associations', 'resource')) {
            Schema::table('owner_associations', function (Blueprint $table) {
                $table->string('resource')->nullable();
            });
        }
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owner_associations', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('oa_number');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
            $table->dropColumn('resource');
        });
    }
};
