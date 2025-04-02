<?php

use App\Models\GlobalSetting;
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
        if (!GlobalSetting::where('id', 1)->exists()) {
            GlobalSetting::create([
                'id' => 1,
                'payment_day' => 30,
                'follow_up_date' => 38,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
