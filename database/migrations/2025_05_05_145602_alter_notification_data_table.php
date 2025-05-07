<?php

use App\Models\Notification;
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
        DB::table('notifications')->whereNull('custom_json_data')->get()->each(function ($notification) {
            DB::table('notifications')->where('id', $notification->id)->update(['custom_json_data' => json_encode(['owner_association_id' => 1])]);
        });
    }
};
