<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Use the specified database connection and table name from the config file
        Schema::connection(config('activitylog.database_connection'))->table(config('activitylog.table_name'), function (Blueprint $table) {
                $table->string('json_hash')->unique(); // Replace 'last_column_name' with the actual column name you want 'json_hash' to appear after
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Use the specified database connection and table name from the config file
        Schema::connection(config('activitylog.database_connection'))->table(config('activitylog.table_name'), function (Blueprint $table) {
                $table->dropUnique(['json_hash']); // Drop unique index first
                $table->dropColumn('json_hash');   // Drop the column
        });
    }
};
