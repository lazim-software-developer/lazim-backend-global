<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class NotificationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Access Card',
            'Announcement',
            'Complaint',
            'Contract',
            'Document',
            'ServiceBooking',
            'FitOutForm',
            'Guest',
            'Invoice',
            'Item',
            'Move in',
            'Move out',
            'Patrolling',
            'Poll',
            'Post',
            'Proposal',
            'ResidentialForm',
            'SaleNoc',
            'UserApproval',
            'Vendor',
            'WDA',
            'Enquiry',
            'Snag',
            'Comment',
            'Help Desk',
            'Owner Association',
            'Payment',
            'Amentity Bbooking',
            'Notice',
            'Tenant Complaint',
            'Suggestion'
        ];

        foreach ($data as $name) {
            DB::table('notification_types')->insert([
                'name' => $name,
                'key' => Str::snake($name),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
