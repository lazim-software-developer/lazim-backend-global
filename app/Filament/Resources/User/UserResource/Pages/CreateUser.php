<?php

namespace App\Filament\Resources\User\UserResource\Pages;
use App\Filament\Forms\Components\Component;

use App\Filament\Resources\User\UserResource;
use App\Jobs\AccountCreationJob;
use App\Jobs\AccountsManagerJob;
use App\Jobs\BuildingSecurity;
use App\Jobs\GeneralAccountCreationJob;
use App\Jobs\MdCreateJob;
use App\Jobs\TechnicianAccountCreationJob;
use App\Jobs\VendorAccountCreationJob;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Filament\Actions;
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

        $roleJobMap = [
            // 'Vendor' => VendorAccountCreationJob::class,
            'Building Engineer' => AccountsManagerJob::class,
            // 'OA' => AccountCreationJob::class,
            // 'Security' => BuildingSecurity::class,
            // 'Technician' => TechnicianAccountCreationJob::class,
            'Accounts Manager' => AccountsManagerJob::class,
            'MD' => MdCreateJob::class,
            'Complaint Officer' => AccountsManagerJob::class,
            'Legal Officer' => AccountsManagerJob::class,
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
        $password = Str::random(12);
        $user->email_verified = 1;
        $user->phone_verified = 1;
        $user->owner_association_id = auth()->user()->owner_association_id;
        $user->password = Hash::make($password);
        $user->role_id = $this->data['roles'];
        $user->save();
        
        // Dispatch the appropriate job based on the role
        if (array_key_exists($this->record->role?->name, $roleJobMap)) {
            $jobClass = $roleJobMap[$this->record->role?->name];
            $jobClass::dispatch($user, $password);
            // GeneralAccountCreationJob::dispatch($user, $password);
        }
        else{
            MdCreateJob::dispatch($user, $password);
        }
    }
}
