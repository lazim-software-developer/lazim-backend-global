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
        Schema::table('complaints', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->dateTime('open_time')->nullable()->change();
            $table->dateTime('close_time')->nullable()->change();
            $table->unsignedBigInteger('oa_user_registration_id');
            $table->foreign('oa_user_registration_id')->references('id')->on('oa_user_registration')->onUpdate('CASCADE')->onDelete('CASCADE');



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->dateTime('open_time')->nullable()->change();
            $table->dateTime('close_time')->nullable()->change();
            $table->unsignedBigInteger('oa_user_registration_id');


        });
    }
};
