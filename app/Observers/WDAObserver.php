<?php

namespace App\Observers;

use App\Filament\Resources\WDAResource;
use App\Models\Accounting\WDA;
use App\Models\Building\Building;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class WDAObserver
{
    /**
     * Handle the WDA "created" event.
     */
    public function created(WDA $WDA): void
    {
        $vendor = DB::table('building_vendor')->where('building_id', $WDA->building_id)
            ->where('vendor_id', $WDA->vendor_id)->first();
        if ($vendor) {
            $requiredPermissions = ['view_any_w::d::a'];
            $building = Building::where('id', $vendor->building_id)->first();
            $notifyTo = User::where('owner_association_id', $building->owner_association_id)->get()
            ->filter(function ($notifyTo) use ($requiredPermissions) {
                return $notifyTo->can($requiredPermissions);
            });
            Notification::make()
                ->success()
                ->title("New WDA Form")
                ->icon('heroicon-o-document-text')
                ->iconColor('warning')
                ->body('New WDA form submitted by  ' . auth()->user()->first_name)
                ->actions([
                    Action::make('view')
                        ->button()
                        ->url(fn () => WDAResource::getUrl('edit', [$WDA])),
                ])
                ->sendToDatabase($notifyTo);
        }

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
                        'title' => 'WDA status update.',
                        'duration' => 'persistent',
                        'icon' => 'heroicon-o-document-text',
                        'iconColor' => 'warning',
                        'body' => 'Your WDA has been approved.',
                        'view' => 'notifications::notification',
                        'viewData' => [],
                        'format' => 'filament',
                        'url' => '',
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
                        'title' => 'WDA status update.',
                        'duration' => 'persistent',
                        'icon' => 'heroicon-o-document-text',
                        'iconColor' => 'warning',
                        'body' => 'Your WDA has been rejected.',
                        'view' => 'notifications::notification',
                        'viewData' => [],
                        'format' => 'filament',
                        'url' => '',
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
