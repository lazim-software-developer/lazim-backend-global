<?php

namespace App\Filament\Resources\OwnerAssociationResource\Pages;

use App\Filament\Resources\OwnerAssociationResource;
use App\Jobs\AccountCreationJob;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EditOwnerAssociation extends EditRecord
{
    protected static string $resource = OwnerAssociationResource::class;
    protected ?string $heading = 'Owner Association';

    public $value;
    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
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
        // $phone = OwnerAssociation::where('id',$this->data['id'])->pluck('phone');
        // dd($phone->first() == $this->data['phone']);

        if($this->record->profile_photo != User::where('owner_association_id', $this->data['id'])->where('role_id', Role::where('name', 'OA')->where('owner_association_id', $this->record->id)->first()->id)->first()->profile_photo){
            $user = User::where('owner_association_id', $this->data['id'])->where('role_id', Role::where('name', 'OA')->where('owner_association_id', $this->record->id)->first()->id)
                ->update([
                    'profile_photo' => $this->record->profile_photo,
                ]);
        }


        // If updated value of verified is true and the value is DB is false(This happens only for the first time)
        if ($this->record->verified == 'true' && DB::table('owner_associations')->where('id', $this->record->id)->value('verified_by') == null) {
            // Update verified in owner_association table
            // OwnerAssociation::where('id', $this->data['id'])
            //     ->update([
            //         'verified_by' => auth()->user()->id,
            //     ]);
            // adding roles
            $oaId = $this->record->id;
            $roles = [
                ['name' => 'Owner', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
                ['name' => 'Vendor', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
                ['name' => 'Managing Director', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
                ['name' => 'Financial Manager', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
                ['name' => 'Building Engineer', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
                ['name' => 'Operations Engineer', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
                ['name' => 'Operations Manager', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
                ['name' => 'Staff', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
                // ['name' => 'Admin', 'owner_association_id' => $oaId,'guard_name' => 'web'],
                ['name' => 'OA', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
                ['name' => 'Tenant', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
                ['name' => 'Security', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
                ['name' => 'Technician', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
                ['name' => 'Accounts Manager', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
                ['name' => 'MD', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
                ['name' => 'Complaint Officer', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
                ['name' => 'Legal Officer', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
            ];
            DB::table('roles')->insert($roles);

            $owner = OwnerAssociation::where('id', $this->record->id)
                ->update([
                    'name'    => $this->record->name,
                    'phone'   => $this->record->phone,
                    'address' => $this->record->address,
                    'active'  => $this->record->active,
                    'verified_by' => auth()->user()->id,
                    'profile_photo' => $this->record->profile_photo,
                ]);

            $user = User::where('owner_association_id', $this->data['id'])->where('role_id', Role::where('name', 'OA')->where('owner_association_id', $oaId)->first()->id)
                ->update([
                    'first_name' => $this->record->name,
                    'phone'      => $this->record->phone,
                    'profile_photo' => $this->record->profile_photo,
                    'active'  => $this->record->active,
                ]);



            $permissionsConfig = config('role-permission');
            Log::info('oa_id' . $this->record->id);
            foreach ($permissionsConfig['roles'] as $roleName => $roleConfig) {
                $role = Role::where('name', $roleName)->where('owner_association_id', $this->record->id)->first();
                Log::info("Role" . $role);
                if (isset($roleConfig['permissions'])) {
                    $role->syncPermissions($roleConfig['permissions']);
                }
            }
            $allowedRoles = ['MD', 'OA', 'Owner', 'Vendor', 'Tenant', 'Technician', 'Security'];
            foreach ($allowedRoles as $role) {
                $userRole = Role::where('name', $role)->where('owner_association_id', $this->record->id)->first();
                $permission = Permission::all();
                $userRole->syncPermissions($permission);
            }


            // Create an entry in Users table
            // check if entered email and phone number is already present for other users in users table
            $emailexists = User::where(['email' => $this->record->email, 'phone' => $this->record->phone])->exists();
            if (!$emailexists) {
                $password = Str::random(12);

                $user = User::firstorcreate(
                    [
                        'email'                => $this->record->email,
                        'phone'                => $this->record->phone,
                    ],
                    [
                        'first_name'           => $this->record->name,
                        'profile_photo'        => $this->record->profile_photo,
                        'role_id'              => Role::where('name', 'OA')->where('owner_association_id', $oaId)->value('id'),
                        'active'               => $this->record->active,
                        'password' => Hash::make($password),
                        'owner_association_id' => $this->record->id,
                        'email_verified' => 1,
                        'phone_verified' => 1,
                    ]
                );
                $user->ownerAssociation()->attach($this->record->id, ['from' => now()->toDateString()]);
                $oa = Role::where('name', 'OA')->where('owner_association_id', $this->record->id)->first();
                DB::table('model_has_roles')->insert([
                    'role_id' => $oa->id,
                    'model_type' => User::class,
                    'model_id' => $user->id,
                ]);

                // $building_id = DB::table('building_owner_association')->where('owner_association_id' , $this->record->id)->first()?->building_id;
                // $connection = DB::connection('lazim_accounts');
                // $connection->table('users')->insert([
                //     'name' => $this->record->name,
                //     'email'                => $this->record->email,
                //     'email_verified_at' => now(),
                //     'password'             => Hash::make($password),
                //     'type' => 'company',
                //     'lang' => 'en',
                //     'created_by' => 1,
                //     'plan' => 1,
                //     'owner_association_id' => $this->record->id,
                //     'building_id' => $building_id,
                //     'created_at' => now(),
                //     'updated_at' => now()
                // ]);
                // $accountUser = $connection->table('users')->where('email',$this->record->email)->where('owner_association_id',$this->record->id )->first();
                // $role = $connection->table('roles')->where('name', 'company')->first();
                // $connection->table('model_has_roles')->insert([
                //     'role_id' => $role?->id,
                //     'model_type' => 'App\Models\User',
                //     'model_id' => $accountUser?->id,
                // ]);

                $connection = DB::connection('lazim_accounts');
                $accountUser = $connection->table('users')
                    ->where('email', $this->record->email)
                    ->where('owner_association_id', $this->record->id)
                    ->first();

                if ($accountUser) {
                    $connection->table('users')
                        ->where('email', $this->record->email)
                        ->where('owner_association_id', $this->record->id)
                        ->update(['password' => Hash::make($password)]);
                }
                // Send email with credentials
                $slug = $this->record->slug;
                AccountCreationJob::dispatch($user, $password,$slug);
            } else {
                $user->ownerAssociation()->attach($this->record->id, ['from' => now()->toDateString()]);
                // No need to handle this - Subhash
            }
        }

        // if account is verified and other fields are updated

    }
}
