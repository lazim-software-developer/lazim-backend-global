<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The connection name for the migration
     * 
     * @var string
     */
    protected $connection;

    /**
     * Create a new migration instance.
     */
    public function __construct()
    {
        // Use the connection from environment variable
        $this->connection = env('SECOND_DB_CONNECTION');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        // Use the specified connection
        Schema::connection($this->connection)->table('customers', function (Blueprint $table) {
            // Add your column here
            $table->softDeletes();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Drop the column if migration needs to be rolled back
        Schema::connection($this->connection)->table('customers', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
