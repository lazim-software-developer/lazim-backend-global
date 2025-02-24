<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEventColumnToActivityLogTable extends Migration
{
    public function up()
    {
        if (Schema::connection(config('activitylog.database_connection'))->hasTable(config('activitylog.table_name'))) {
            Schema::connection(config('activitylog.database_connection'))->table(config('activitylog.table_name'), function (Blueprint $table) {
                if (!Schema::connection(config('activitylog.database_connection'))->hasColumn(config('activitylog.table_name'), 'event')) {
                    $table->string('event')->nullable()->after('subject_type');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::connection(config('activitylog.database_connection'))->hasTable(config('activitylog.table_name'))) {
            Schema::connection(config('activitylog.database_connection'))->table(config('activitylog.table_name'), function (Blueprint $table) {
                $table->dropColumn('event');
            });
        }
    }
}
