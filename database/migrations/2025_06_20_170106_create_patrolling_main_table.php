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
        Schema::create('patrolling_records', function (Blueprint $table) { // table name changed to 'patrolling_records' for clarity as patrolling table is already used for patrolling sessions
            $table->id();
            $table->unsignedBigInteger('building_id')->index();
            $table->tinyInteger('is_completed')->default(0)->comment('0: not completed, 1: Completed');
            $table->unsignedBigInteger('patrolled_by')->index()->comment('User ID of the person who patrolled');
            $table->unsignedBigInteger('owner_association_id')->index();
            $table->integer('total_count')->default(0)->comment('Total number of QR to be patrolled');
            $table->integer('completed_count')->default(0)->comment('Total number of QR patrolled');
            $table->integer('pending_count')->default(0)->comment('Total number of QR scanned');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');
            $table->foreign('patrolled_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('owner_association_id')->references('id')->on('owner_associations')->onDelete('cascade');
        });

        Schema::table('patrollings', function (Blueprint $table) {
            $table->tinyInteger('is_completed')->default(0)->comment('0: not completed, 1: Completed');
            $table->unsignedBigInteger('patrolling_record_id')->nullable()->index()->after('id');
            $table->unsignedBigInteger('location_id')->nullable()->after('floor_id');
            $table->string('location_name')->nullable()->after('location_id');
             $table->datetime('patrolled_at')->nullable()->change(); // Change patrolled_at to nullable

            $table->foreign('patrolling_record_id')->references('id')->on('patrolling_records')->onDelete('cascade');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patrolling_records');
        Schema::table('patrollings', function (Blueprint $table) {
            $table->dropForeign(['patrolling_record_id']);
            $table->dropColumn('is_completed');
            $table->dropColumn('patrolling_record_id');
            $table->dropColumn('location_name');
            $table->dropColumn('location_id');
        });
    }
};
