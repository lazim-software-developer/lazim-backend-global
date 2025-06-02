<?php

namespace App\Observers;

use App\Models\Asset;
use App\Models\ExpoPushNotification;
use App\Models\TechnicianAssets;
use App\Traits\UtilsTrait;
use Illuminate\Support\Facades\DB;

class TechnicianAssetsObserver
{
    use UtilsTrait;
    /**
     * Handle the TechnicianAssets "created" event.
     */
    public function created(TechnicianAssets $technicianAssets): void
    {
        $assetName = Asset::where('id', $technicianAssets->asset_id)->first();
        $expoPushTokens = ExpoPushNotification::where('user_id', $technicianAssets->technician_id)->pluck('token');
        if ($expoPushTokens->count() > 0) {
            foreach ($expoPushTokens as $expoPushToken) {
                $message = [
                    'to' => $expoPushToken,
                    'sound' => 'default',
                    'title' => 'New Asset Assigned',
                    'body' => 'A new Asset '.$assetName->name.' has been added to you. ',
                    'data' => ['notificationType' => 'AssestTab',
                            'building_id' => $technicianAssets->building_id],
                ];
                $this->expoNotification($message);
                DB::table('notifications')->insert([
                    'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                    'type' => 'Filament\Notifications\DatabaseNotification',
                    'notifiable_type' => 'App\Models\User\User',
                    'notifiable_id' => $technicianAssets->technician_id,
                    'custom_json_data' => json_encode([
                        'owner_association_id' => $technicianAssets->building->owner_association_id,
                        'building_id' => $technicianAssets->building_id,
                        'technician_assets_id' => $technicianAssets->id,
                        'user_id' => auth()->user()->id ?? null,
                        'type' => 'TechnicianAssets',
                        'priority' => 'Medium',
                    ]),
                    'data' => json_encode([
                        'actions' => [],
                        'body' => 'A new Asset '.$assetName->name.' has been added to you. ',
                        'duration' => 'persistent',
                        'icon' => 'heroicon-o-document-text',
                        'iconColor' => 'warning',
                        'title' => 'New Asset Assigned',
                        'view' => 'notifications::notification',
                        'viewData' => ['building_id' => $technicianAssets->building_id,],
                        'format' => 'filament',
                        'url' => 'AssestTab',
                    ]),
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    /**
     * Handle the TechnicianAssets "updated" event.
     */
    public function updated(TechnicianAssets $technicianAssets): void
    {
        //
    }

    /**
     * Handle the TechnicianAssets "deleted" event.
     */
    public function deleted(TechnicianAssets $technicianAssets): void
    {
        //
    }

    /**
     * Handle the TechnicianAssets "restored" event.
     */
    public function restored(TechnicianAssets $technicianAssets): void
    {
        //
    }

    /**
     * Handle the TechnicianAssets "force deleted" event.
     */
    public function forceDeleted(TechnicianAssets $technicianAssets): void
    {
        //
    }
}
