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
        Schema::create('notification_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->softDeletes('deleted_at');
            $table->timestamps();
        });
        
        $data = [
            ['name' => 'Access Card'],
            ['name' => 'Announcement'],
            ['name' => 'Complaint'],
            ['name' => 'Contract'],
            ['name' => 'Document'],
            ['name' => 'ServiceBooking'],
            ['name' => 'FitOutForm'],
            ['name' => 'Guest'],
            ['name' => 'Invoice'],
            ['name' => 'Item'],
            ['name' => 'Move in'],
            ['name' => 'Move out'],
            ['name' => 'Patrolling'],
            ['name' => 'Poll'],
            ['name' => 'Post'],
            ['name' => 'Proposal'],
            ['name' => 'ResidentialForm'],
            ['name' => 'SaleNoc'],
            ['name' => 'UserApproval'],
            ['name' => 'Vendor'],
            ['name' => 'WDA'],
        ];
        
        foreach ($data as $item) {
            $item['created_at'] = now();
            $item['updated_at'] = now();
            DB::table('notification_types')->insert($item);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_types');
    }
};
