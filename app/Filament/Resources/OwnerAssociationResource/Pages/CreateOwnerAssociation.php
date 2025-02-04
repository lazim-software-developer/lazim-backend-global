<?php

namespace App\Filament\Resources\OwnerAssociationResource\Pages;

use Filament\Actions;
use App\Models\User\User;
use Illuminate\Support\Str;
use App\Jobs\AccountCreationJob;
use App\Models\OwnerAssociation;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\OwnerAssociationResource;

class CreateOwnerAssociation extends CreateRecord
{
    protected ?string $heading        = 'Owner Association';

    protected static string $resource = OwnerAssociationResource::class;

    public function afterCreate()
    {
        $owner = OwnerAssociation::where('id', $this->record->id)
                ->update([
                    'verified'    => 1,
                ]);
        $this->AssignedPermisionToOwnerAssociation($this->record->id);
        $this->CreateUser($this->record);
    }

    public function AssignedPermisionToOwnerAssociation($id)
    {
        $oaId = $id;
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

        $permissionsConfig = config('role-permission');
        Log::info('oa_id' . $oaId);
        foreach ($permissionsConfig['roles'] as $roleName => $roleConfig) {
            $role = Role::where('name', $roleName)->where('owner_association_id', $oaId)->first();
            Log::info("Role" . $role);
            if (isset($roleConfig['permissions'])) {
                $role->syncPermissions($roleConfig['permissions']);
            }
        }
        $allowedRoles = ['MD', 'OA', 'Owner', 'Vendor', 'Tenant', 'Technician', 'Security'];
        foreach ($allowedRoles as $role) {
            $userRole = Role::where('name', $role)->where('owner_association_id', $oaId)->first();
            $permission = Permission::all();
            $userRole->syncPermissions($permission);
        }
    }
    public function CreateUser($data)
    {

        // Create an entry in Users table
        // check if entered email and phone number is already present for other users in users table
        $emailexists = User::where(['email' => $data->email, 'phone' => $data->phone])->exists();
        if (!$emailexists) {
            $password = $data->password;
            $user = User::firstorcreate(
                [
                    'email'                => $data->email,
                    'phone'                => $data->phone,
                ],
                [
                    'first_name'           => $data->name,
                    'profile_photo'        => $data->profile_photo,
                    'role_id'              => Role::where('name', 'OA')->where('owner_association_id', $data->id)->value('id'),
                    'active'               => 1,
                    'password' => $password,
                    'owner_association_id' => $data->id,
                    'email_verified' => 1,
                    'phone_verified' => 1,
                ]
            );
            $user->ownerAssociation()->attach($data->id, ['from' => now()->toDateString()]);
            $oa = Role::where('name', 'OA')->where('owner_association_id', $data->id)->first();
            DB::table('model_has_roles')->insert([
                'role_id' => $oa->id,
                'model_type' => User::class,
                'model_id' => $user->id,
            ]);
            $this->LazimAccountDatabase($data, $user, $password);
            // Send email with credentials
            $slug = $data->slug;
            AccountCreationJob::dispatch($user, $password, $slug);
        } 
    }

    public function LazimAccountDatabase($data, $user, $password) {
        $connection = DB::connection('lazim_accounts');
        $building_id = DB::table('building_owner_association')->where('owner_association_id' , $data->id)->first()?->building_id;
        $connection = DB::connection('lazim_accounts');
        $connection->table('users')->insert([
            'name' => $data->name,
            'email'                => $data->email,
            'email_verified_at' => now(),
            'password'             => $password,
            'type' => 'company',
            'lang' => 'en',
            'created_by' => auth()->user()->id,
            'plan' => 1,
            'owner_association_id' => $data->id,
            'building_id' => $building_id??NULL,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $accountUser = $connection->table('users')->where('email',$data->email)->where('owner_association_id',$data->id )->first();
        $role = $connection->table('roles')->where('name', 'company')->first();
        $connection->table('model_has_roles')->insertOrIgnore([
            'role_id' => $role?->id,
            'model_type' => 'App\Models\User',
            'model_id' => $accountUser?->id,
        ]);
    }
}
