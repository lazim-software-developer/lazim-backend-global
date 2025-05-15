<?php

namespace App\Filament\Resources\OwnerAssociationResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use App\Models\User\User;
use App\Models\Master\Role;
use Filament\Actions\Action;
use App\Models\ApartmentOwner;
use App\Jobs\AccountCreationJob;
use App\Models\OwnerAssociation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\WelcomeNotificationJob;
use Illuminate\Support\Facades\Http;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\OwnerAssociationResource;

class ListOwnerAssociations extends ListRecords
{
    protected static string $resource = OwnerAssociationResource::class;
    protected ?string $heading        = 'Owner Association';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Sync Owner Association from Mollak')
            ->icon('heroicon-o-information-circle')
            ->disabled(function (): bool {
                // Get the latest record for this user
                $lastSync = DB::table('mollak_api_call_histories')
                    ->where('module', 'OwnerAssociation')
                    ->where('job_name', 'SyncOwnerAssociations')
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
                $lastSync = DB::table('mollak_api_call_histories')->where('module', 'Owner')->where('job_name', 'FetchOwnersForFlat')->where('user_id', auth()->user()->id)->orderBy('created_at', 'DESC')->first();
                
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
                            $el.innerHTML = "Sync Owner Association from Mollak<div class=\'text-xs mt-1 opacity-75\'>Last Sync: " + this.lastSync + "</div>";
                        }
                    }'
                ];
            })
                ->visible(function () {
                    $auth_user = auth()->user();
                    $role      = Role::where('id', $auth_user->role_id)->first()?->name;

                    if ($role === 'Admin') {
                        return true;
                    }
                })
                ->action(function () {
                    $response = Http::withoutVerifying()->withHeaders([
                        'content-type' => 'application/json',
                        'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
                    ])->get(env("MOLLAK_API_URL") . '/sync/managementcompany');
            
                    $managementCompanies = $response->json()['response']['managementCompanies'];
            
            
                    foreach ($managementCompanies as $company) {
                        $ownerAssociation = OwnerAssociation::firstOrCreate(
                            [
                                'mollak_id' => $company['id'],
                                'trn_number' => $company['trn']
                            ],
                            [
                                'name'       => $company['name']['englishName'],
                                'phone'      => $company['contactNumber'],
                                'email'      => $company['email'],
                                'trn_number' => $company['trn'],
                                'address'    => $company['address'],
                            ]
                        );
                        $emailexists = User::where(['email' => $company['email'], 'phone' => $company['contactNumber']])->exists();
                        if (!$emailexists) {
                            $password = 'Password';
                            $user = User::firstorcreate(
                                [
                                    'email'                => $company['email'],
                                    'phone'                => $company['contactNumber'],
                                ],
                                [
                                    'first_name'           => $company['name']['englishName'],
                                    'profile_photo'        => $company['logo'] ?? null,
                                    'role_id'              => Role::where('name', 'OA')->value('id'),
                                    'active'               => 1,
                                    'password' => $password,
                                    'owner_association_id' => $ownerAssociation->id,
                                    'email_verified' => 1,
                                    'phone_verified' => 1,
                                ]
                            );
                            $user->ownerAssociation()->attach($ownerAssociation->id, ['from' => now()->toDateString()]);
                            $oa = Role::where('name', 'OA')->first();
                            DB::table('model_has_roles')->insert([
                                'role_id' => $oa->id,
                                'model_type' => User::class,
                                'model_id' => $user->id,
                            ]);
                            $connection = DB::connection(env('SECOND_DB_CONNECTION'));
                            $building_id = DB::table('building_owner_association')->where('owner_association_id' , $ownerAssociation->id)->first()?->building_id;
                            $connection->table('users')->insert([
                                'name' => $ownerAssociation->name,
                                'email'                => $ownerAssociation->email,
                                'email_verified_at' => now(),
                                'password'             => $password,
                                'type' => 'company',
                                'lang' => 'en',
                                'created_by' => auth()->user()->id,
                                'plan' => 1,
                                'owner_association_id' => $ownerAssociation->id,
                                'building_id' => $building_id ?? NULL,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            $accountUser = $connection->table('users')->where('email',$ownerAssociation->email)->where('owner_association_id',$ownerAssociation->id )->first();
                            $role = $connection->table('roles')->where('name', 'company')->first();
                            $connection->table('model_has_roles')->insertOrIgnore([
                                'role_id' => $role?->id,
                                'model_type' => 'App\Models\User',
                                'model_id' => $accountUser?->id,
                            ]);
                            // Send email with credentials
                            $slug = $ownerAssociation->slug;
                            AccountCreationJob::dispatch($user, $password, $slug);
                        }
                        try {
                            $url = 'api/register';
                            $body = [
                                'name'      => $company['name']['englishName'],
                                'email'     => $company['email'],
                                'password'  => 'Password',
                                'password_confirmation' => 'Password',
                                'created_by_lazim' => 1,
                                'owner_association_id' => $ownerAssociation->id,
                            ];
                            $httpRequest  = Http::withOptions(['verify' => false])
                                ->withHeaders([
                                    'Content-Type' => 'application/json',
                                ]);
                            $response = $httpRequest->post(env('ACCOUNTING_URL') . $url, $body);
                            Log::info([$response->json()]);

                        } catch (\Exception $e) {
                            Log::error('Error ' . $e->getMessage());
                        }
                    }
                    DB::table('mollak_api_call_histories')->insert([
                        'api_url'     => '/sync/managementcompany',
                        'module'      => 'OwnerAssociation',
                        'job_name'    => 'SyncOwnerAssociations',
                        'user_id'     => auth()->user()->id,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    Notification::make()
                        ->title('Owner Successfully Synced From Mollak')
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make()
            ->visible(fn () => auth()->user()->hasRole('Admin')),

        ];
    }
    protected function getTableQuery(): Builder
    {
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
            return parent::getTableQuery()->where('role','!=', 'Property Manager');
        }
        return parent::getTableQuery()->where('id', auth()->user()?->owner_association_id);
    }

}