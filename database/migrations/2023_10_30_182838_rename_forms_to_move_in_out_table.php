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
        Schema::rename('forms', 'move_in_out');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('move_in_out', 'forms');
    }
};
