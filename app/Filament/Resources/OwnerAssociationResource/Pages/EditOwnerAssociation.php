<?php

namespace App\Filament\Resources\OwnerAssociationResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use App\Models\User\User;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use App\Jobs\FetchBuildingsJob;
use App\Jobs\AccountCreationJob;
use App\Models\OwnerAssociation;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Permission;
use App\Filament\Resources\OwnerAssociationResource;

class EditOwnerAssociation extends EditRecord
{
    protected static string $resource = OwnerAssociationResource::class;
    protected ?string $heading = 'Owner Association';

    public $value;
    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Action::make('Sync Buildings from Mollak')
            ->label('Sync Buildings from Mollak')
            ->icon('heroicon-o-information-circle')
            ->disabled(function (): bool {
                // Get the latest record for this user
                $lastSync = DB::table('mollak_api_call_histories')
                    ->where('module', 'Building')
                    ->where('job_name', 'FetchBuildingsJob')
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
                $lastSync = DB::table('mollak_api_call_histories')->where('module', 'Building')->where('job_name', 'FetchBuildingsJob')->where('user_id', auth()->user()->id)->orderBy('created_at', 'DESC')->first();

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
                    'x-data' => '{
                        lastSync: "' . $lastSyncDisplay . '",
                        init() {
                            $el.innerHTML = "Sync Buildings from Mollak<div class=\'text-xs mt-1 opacity-75\'>Last Sync: " + this.lastSync + "</div>";
                        }
                    }'
                ];
            })
            ->visible(function () {
                $auth_user = auth()->user();
                $role      = Role::where('id', $auth_user->role_id)->first()?->name;

                if ($role === 'Admin' || $role === 'Property Manager' || $role === 'OA') {
                    return true;
                }
            })
            ->action(function () {
                $ownerAssociation = OwnerAssociation::where('id', $this->record->id)->first();
                if (!empty($ownerAssociation->mollak_id)) {
                FetchBuildingsJob::dispatch($ownerAssociation, 'Manual');
                DB::table('mollak_api_call_histories')->insert([
                    'api_url'     => '/sync/managementcompany/' . $ownerAssociation->mollak_id . '/propertygroups',
                    'module'      => 'Building',
                    'job_name'    => 'FetchBuildingsJob',
                    'user_id'     => auth()->user()->id,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                Notification::make()
                    ->title('Fetching buildings from Mollak is in progress. Once synced, it will be visible in the list')
                    ->success()
                    ->send();
                }else{
                    Notification::make()
                    ->title('This Owner Association is not found in Mollak')
                    ->warning()
                    ->send();
                }
            }),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    public function beforeSave()
    {
        $email_value = OwnerAssociation::where('id', $this->data['id'])->get();
        $this->value = $email_value->first()->email;
    }

    public function afterSave()
    {
        $this->UpdateUser($this->record);
    }
    public function UpdateUser($data)
    {
        $user = User::where('owner_association_id', $data->id)->where('phone',$data->phone)->where('email',$data->email)
        ->update([
            'first_name' => $data->name,
            'phone'      => $data->phone,
            'profile_photo' => $data->profile_photo,
            'active'  => $data->active,
        ]);

        $connection = DB::connection(env('SECOND_DB_CONNECTION'));
        $connection->table('users')->where('email', $data->email)->where('owner_association_id', $data->id)->update([
            'name' => $data->name,
        ]);
    }
}
