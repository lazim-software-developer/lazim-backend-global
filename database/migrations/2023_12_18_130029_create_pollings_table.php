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
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->json('options');
            $table->enum('status', ['published', 'draft'])->default('draft');
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('ends_on')->nullable();
            $table->foreignId('building_id')->constrained('buildings');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('poll_responses', function (Blueprint $table) {
            $table->id();
            $table->string('answer');
            $table->dateTime('submitted_at');
            $table->foreignId('poll_id')->constrained('polls');
            $table->foreignId('submitted_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pollings');
    }
};
