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
    }
}
