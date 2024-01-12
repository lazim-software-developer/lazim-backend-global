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
        Schema::create('item_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->nullable()->constrained('items');
            $table->dateTime('date');
            $table->string('type');
            $table->integer('quantity')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->string('comments');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_inventory');
    }
};
