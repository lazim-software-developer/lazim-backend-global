<?php

namespace App\Observers;

use App\Models\Accounting\WDA;
use App\Models\Building\Building;
use App\Models\User\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class WDAObserver
{
    /**
     * Handle the WDA "created" event.
     */
    public function created(WDA $wDA): void
    {
        $vendor = DB::table('building_vendor')->where('building_id', $wDA->building_id)
                    ->where('vendor_id',$wDA->vendor_id)->first();
        $building = Building::where('id', $vendor->building_id)->first();
        $notifyTo = User::where('owner_association_id', $building->owner_association_id)->where('role_id',10)->get();
            Notification::make()
            ->success()
            ->title("New WDA Form")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('New WDA form submitted by  '.auth()->user()->first_name)
            ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the WDA "updated" event.
     */
    public function updated(WDA $wDA): void
    {
        $user = auth()->user();
        if ($user->role->name == 'OA') {
            if ($wDA->status == 'approved') {
                DB::table('notifications')->insert([
                    'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                    'type' => 'Filament\Notifications\DatabaseNotification',
                    'notifiable_type' => 'App\Models\User\User',
                    'notifiable_id' => $wDA->created_by,
                    'data' => json_encode([
                        'actions' => [],
                        'title' => 'wDA status update.',
                        'duration' => 'persistent',
                        'icon' => 'heroicon-o-document-text',
                        'iconColor' => 'warning',
                        'body' => 'Your wDA has been approved.',
                        'view' => 'notifications::notification',
                        'viewData' => [],
                        'format' => 'filament',
                    ]),
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]);
            }
            if ($wDA->status == 'rejected') {
                DB::table('notifications')->insert([
                    'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                    'type' => 'Filament\Notifications\DatabaseNotification',
                    'notifiable_type' => 'App\Models\User\User',
                    'notifiable_id' => $wDA->created_by,
                    'data' => json_encode([
                        'actions' => [],
                        'title' => 'wDA status update.',
                        'duration' => 'persistent',
                        'icon' => 'heroicon-o-document-text',
                        'iconColor' => 'warning',
                        'body' => 'Your wDA has been rejected.',
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
     * Handle the WDA "deleted" event.
     */
    public function deleted(WDA $wDA): void
    {
        //
    }

    /**
     * Handle the WDA "restored" event.
     */
    public function restored(WDA $wDA): void
    {
        //
    }

    /**
     * Handle the WDA "force deleted" event.
     */
    public function forceDeleted(WDA $wDA): void
    {
        //
    }
}
