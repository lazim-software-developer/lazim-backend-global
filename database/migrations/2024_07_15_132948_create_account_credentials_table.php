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
        Schema::create('account_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('password')->nullable();
            $table->foreignId('oa_id')->constrained('owner_associations');
            $table->foreignId('created_by')->constrained('users');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_credentials');
    }
};
