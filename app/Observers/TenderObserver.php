<?php

namespace App\Observers;

use App\Models\Accounting\Tender;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Illuminate\Support\Facades\DB;

class TenderObserver
{
    /**
     * Handle the Tender "created" event.
     */
    public function created(Tender $tender): void
    {
        $user = auth()->user();
        $buildingVendor = DB::table('building_vendor')
            ->where('building_id', $tender->building_id)
            ->distinct()->pluck('vendor_id');
        $serviceVendor = DB::table('service_vendor')
            ->where('service_id', $tender->service_id)
            ->whereIn('vendor_id', $buildingVendor)->distinct()->pluck('vendor_id');
        $vendorId = Vendor::whereIn('id', $serviceVendor)->pluck('owner_id');
        foreach($vendorId as $vendor){
            if ($user->role->name == 'OA') {
                DB::table('notifications')->insert([
                    'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                    'type' => 'Filament\Notifications\DatabaseNotification',
                    'notifiable_type' => 'App\Models\User\User',
                    'notifiable_id' => $vendor,
                    'data' => json_encode([
                        'actions' => [],
                        'body' => 'New tender has been created by ' . auth()->user()->first_name,
                        'duration' => 'persistent',
                        'icon' => 'heroicon-o-document-text',
                        'iconColor' => 'warning',
                        'title' => 'New Tender Received',
                        'view' => 'notifications::notification',
                        'viewData' => [],
                        'format' => 'filament',
                    ]),
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    /**
     * Handle the Tender "updated" event.
     */
    public function updated(Tender $tender): void
    {
        //
    }

    /**
     * Handle the Tender "deleted" event.
     */
    public function deleted(Tender $tender): void
    {
        //
    }

    /**
     * Handle the Tender "restored" event.
     */
    public function restored(Tender $tender): void
    {
        //
    }

    /**
     * Handle the Tender "force deleted" event.
     */
    public function forceDeleted(Tender $tender): void
    {
        //
    }
}
