<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function __construct()
    {
        // Use the connection from environment variable
        $this->connection = env('SECOND_DB_CONNECTION');
    }
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::connection($this->connection)->hasColumn('users', 'deleted_at')) {
            Schema::connection($this->connection)->table('users', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
