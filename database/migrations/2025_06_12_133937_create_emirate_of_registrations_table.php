<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('emirate_of_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->string('status')->default('0');
            $table->softDeletes('deleted_at');
            $table->timestamps();
        });
        
        $emirate_of_registrations = [
            ['name' => 'Abu Dhabi', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Dubai', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sharjah', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ajman', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ras Al Khaimah', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fujairah', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Umm Al Quwain', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
        ];
        
        DB::table('emirate_of_registrations')->insert($emirate_of_registrations);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emirate_of_registrations');
    }
};
