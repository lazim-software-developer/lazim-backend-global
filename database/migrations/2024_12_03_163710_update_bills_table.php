
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->decimal('amount', 8, 2)->nullable()->change();
            $table->date('due_date')->nullable()->change();
            $table->string('status')->nullable()->change();
            $table->unsignedBigInteger('status_updated_by')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->decimal('amount', 8, 2)->nullable(false)->change();
            $table->date('due_date')->nullable(false)->change();
            $table->string('status')->nullable(false)->change();
            $table->unsignedBigInteger('status_updated_by')->nullable(false)->change();
        });
    }
}
