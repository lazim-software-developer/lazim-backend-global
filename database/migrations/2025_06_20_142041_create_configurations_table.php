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
        Schema::create('configurations', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->unsignedBigInteger('owner_association_id');
            $table->foreign('owner_association_id')->references('id')->on('owner_associations')->onDelete('cascade');
            $table->timestamps();
        });

        DB::table('configurations')->insert([
            [
                'key' => 'access_card_price',
                'value' => '100',
                'owner_association_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configurations');
    }
};
