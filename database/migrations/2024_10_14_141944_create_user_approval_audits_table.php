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
        Schema::create('user_approval_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_approval_id');
            $table->string('status')->nullable();
            $table->longText('remarks')->nullable();
            $table->longText('document');
            $table->string('document_type');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->longText('emirates_document')->nullable();
            $table->longText('passport')->nullable();
            $table->unsignedBigInteger('owner_association_id')->nullable();

            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('user_approval_id')->references('id')->on('user_approvals');
            $table->foreign('owner_association_id')->references('id')->on('owner_associations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_approval_audits');
    }
};
