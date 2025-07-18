<?php

namespace App\Filament\Resources\Building\FlatResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use App\Models\Master\Role;
use Filament\Actions\Action;
use App\Models\Building\Flat;
use App\Jobs\FetchOwnersForFlat;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Building\FlatResource;

class EditFlat extends EditRecord
{
    protected static string $resource = FlatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), # TODO: Change this to the correct association ID or condition
            Action::make('Sync Owner from Mollak')
            ->icon('heroicon-o-information-circle')
            ->disabled(function (): bool {
                // Get the latest record for this user
                $lastSync = DB::table('mollak_api_call_histories')
                    ->where('module', 'Owner')
                    ->where('job_name', 'FetchOwnersForFlat')
                    ->where('record_id', $this->record->id)
                    ->where('user_id', auth()->user()->id)
                    ->orderBy('created_at', 'DESC')
                    ->first();

                // If no record exists, enable the button (return false for disabled)
                if (!$lastSync) {
                    return false;
                }

                // If record exists, check if it's less than 30 minutes old
                return now()->diffInMinutes(Carbon::parse($lastSync->created_at)) < 30;
            })
            ->extraAttributes(function () {
                // Get the last sync time from database
                $lastSync = DB::table('mollak_api_call_histories')->where('module', 'Owner')->where('job_name', 'FetchOwnersForFlat')->where('record_id', $this->record->id)->where('user_id', auth()->user()->id)->orderBy('created_at', 'DESC')->first();

                // Default value if no sync history exists
                $lastSyncDisplay = 'Never synced';
                $lastSyncTime = now()->format('Y-m-d H:i:s');

                if ($lastSync) {
                    $lastSyncTime = $lastSync->created_at;

                    // Format the display text based on time difference
                    $diffInMinutes = now()->diffInMinutes($lastSyncTime);
                    if ($diffInMinutes < 60) {
                        $lastSyncDisplay = $diffInMinutes . ' minutes ago';
                    } else {
                        $diffInHours = now()->diffInHours($lastSyncTime);
                        if ($diffInHours < 24) {
                            $lastSyncDisplay = $diffInHours . ' hours ago';
                        } else {
                            $lastSyncDisplay = Carbon::parse($lastSyncTime)->format('Y-m-d H:i:s');
                        }
                    }
                }

                return [
                    'title' => 'Last Sync: ' . $lastSyncDisplay,
                    'class' => 'relative',
                    'x-data' => `{
                        lastSync: "' . $lastSyncDisplay . '",
                        init() {
                            $+el.innerHTML = "Sync Owner from Mollak<div class=\'text-xs mt-1 opacity-75\'>Last Sync: " + this.lastSync + "</div>";
                        }
                    }`
                    // 'x-data' => '{
                    //     lastSync: "' . $lastSyncDisplay . '",
                    //     init() {
                    //         $el.innerHTML = "Sync Owner from Mollak<div class=\'text-xs mt-1 opacity-75\'>Last Sync: " + this.lastSync + "</div>";
                    //     }
                    // }'
                ];
            })
                ->visible(function () {
                    $auth_user = auth()->user();
                    $role      = Role::where('id', $auth_user->role_id)->first()?->name;

                    // if ($role === 'Admin' || $role === 'OA' || $role === 'Customer Service Manager' ) {
                    if (in_array($role,['Admin','OA','Customer Service Manager','Customer Service '])) {
                        return true;
                    }
                })
                ->action(function () {
                    $flat = Flat::where('id', $this->record->id)->first();
                    if(!empty($flat->mollak_property_id)){
                    FetchOwnersForFlat::dispatch($flat);
                    DB::table('mollak_api_call_histories')->insert([
                        'api_url'     => '/sync/owners/' . $flat->building->property_group_id . "/" . $flat->mollak_property_id,
                        'module'      => 'Owner',
                        'job_name'    => 'FetchOwnersForFlat',
                        'record_id'   => $flat->id,
                        'user_id'     => auth()->user()->id,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    Notification::make()
                        ->title('Fetching owners from Mollak is in progress. Once synced, it will be visible in the list')
                        ->success()
                        ->send();
                    }else{
                        Notification::make()
                        ->title('This Flat is not found in Mollak')
                        ->warning()
                        ->send();
                    }
                })
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
