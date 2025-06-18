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
        Schema::create('service_request_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('record_id')->nullable()->comment('The ID of the record being modified');
            $table->string('type', 50)->nullable()->comment('Type of service request');
            $table->string('action', 50)->nullable()->comment('Action performed (create/update/delete)');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('User who performed the action');
            $table->timestamp('action_at')->nullable()->comment('When the action occurred');
            $table->json('request_json')->nullable()->comment('JSON payload of the request');
            $table->timestamps();
            
            // Optional index for better query performance
            $table->index(['record_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_request_histories');
    }
};
