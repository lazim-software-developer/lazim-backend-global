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
        Schema::table('owner_association_receipts', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'overdue'])->nullable();
        });
    }

    public function down()
    {
        Schema::table('owner_association_receipts', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
