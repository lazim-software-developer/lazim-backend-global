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
        Schema::table('oa_service_requests', function (Blueprint $table) {
            $table
                ->foreign('service_parameter_id')
                ->references('id')
                ->on('service_parameters')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table
                ->foreign('uploaded_by')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oa_service_request', function (Blueprint $table) {
            $table->dropColumn('service_parameter_id');
            $table->dropColumn('uploaded_by');
        });
    }
};
