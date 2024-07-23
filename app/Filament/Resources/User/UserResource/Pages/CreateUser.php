<?php

namespace App\Filament\Resources\User\UserResource\Pages;

use App\Filament\Resources\User\UserResource;
use App\Jobs\AccountsManagerJob;
use App\Jobs\MdCreateJob;
use App\Models\AccountCredentials;
use App\Models\OwnerAssociation;
use App\Models\OwnerAssociationUser;
use App\Models\User\User;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function beforeCreate(): void
    {
        // dd($this->data['roles']);
        // $role_id = $this->data['roles'];

        // return $data;
    }
    protected function afterCreate()
    {
        // dd($this->data);
        $user = User::find($this->record->id);
        OwnerAssociationUser::create([
            'owner_association_id' => $this->record->owner_association_id,
            'user_id'              => $this->record->id,
            'from'                 => now(),
        ]);

        $roleJobMap = [
            // 'Vendor' => VendorAccountCreationJob::class,
            'Building Engineer' => AccountsManagerJob::class,
            // 'OA' => AccountCreationJob::class,
            // 'Security' => BuildingSecurity::class,
            // 'Technician' => TechnicianAccountCreationJob::class,
            'Accounts Manager'  => AccountsManagerJob::class,
            'MD'                => MdCreateJob::class,
            'Complaint Officer' => AccountsManagerJob::class,
            'Legal Officer'     => AccountsManagerJob::class,
            // 'Managing Director' => GeneralAccountCreationJob::class,
            // 'Financial Manager' => GeneralAccountCreationJob::class,
            // 'Operations Engineer' => GeneralAccountCreationJob::class,
            // 'Owner' => GeneralAccountCreationJob::class,
            // 'Tenant' => GeneralAccountCreationJob::class,
            // 'Operations Manager' => GeneralAccountCreationJob::class,
            // 'Staff' => GeneralAccountCreationJob::class,
            // 'Admin' => GeneralAccountCreationJob::class,
        ];

        // Generate and set the password
        $password                   = Str::random(12);
        $user->email_verified       = 1;
        $user->phone_verified       = 1;
        $user->owner_association_id = auth()->user()->owner_association_id;
        $user->password             = Hash::make($password);
        $user->role_id              = $this->data['roles'];
        $user->save();

        // Dispatch the appropriate job based on the role
        if (array_key_exists($this->record->role?->name, $roleJobMap)) {
            $jobClass         = $roleJobMap[$this->record->role?->name];
            $tenant           = Filament::getTenant()?->id ?? auth()->user()->owner_association_id;
            // $emailCredentials = OwnerAssociation::find($tenant)?->accountcredentials()->where('active', true)->latest()->first()->email ?? env('MAIL_FROM_ADDRESS');

            $credentials = AccountCredentials::where('oa_id', $tenant)->where('active', true)->latest()->first();
            $mailCredentials = [
                'mail_host' => $credentials->host ?? env('MAIL_HOST'),
                'mail_port' => $credentials->port ?? env('MAIL_PORT'),
                'mail_username' => $credentials->username ?? env('MAIL_USERNAME'),
                'mail_password' => $credentials->password ?? env('MAIL_PASSWORD'),
                'mail_encryption' => $credentials->encryption ?? env('MAIL_ENCRYPTION'),
                'mail_from_address' => $credentials->email ?? env('MAIL_FROM_ADDRESS'),
            ];

            $jobClass::dispatch($user, $password, $mailCredentials);
            // GeneralAccountCreationJob::dispatch($user, $password);
        } else {
            $tenant           = Filament::getTenant()?->id ?? auth()->user()->owner_association_id;
            // $mailCredentials = OwnerAssociation::find($tenant)?->accountcredentials()->where('active', true)->latest()->first()->email ?? env('MAIL_FROM_ADDRESS');

            $credentials = AccountCredentials::where('oa_id', $tenant)->where('active', true)->latest()->first();
            $mailCredentials = [
                'mail_host' => $credentials->host ?? env('MAIL_HOST'),
                'mail_port' => $credentials->port ?? env('MAIL_PORT'),
                'mail_username' => $credentials->username ?? env('MAIL_USERNAME'),
                'mail_password' => $credentials->password ?? env('MAIL_PASSWORD'),
                'mail_encryption' => $credentials->encryption ?? env('MAIL_ENCRYPTION'),
                'mail_from_address' => $credentials->email ?? env('MAIL_FROM_ADDRESS'),
            ];

            MdCreateJob::dispatch($user, $password, $mailCredentials);
        }
    }
}
