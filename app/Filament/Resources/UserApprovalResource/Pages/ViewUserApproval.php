<?php

namespace App\Filament\Resources\UserApprovalResource\Pages;

use App\Filament\Resources\UserApprovalResource;
use App\Models\User\User;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUserApproval extends ViewRecord
{
    protected static string $resource = UserApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
{
    $user = User::find($data['user_id']);
    $data['user'] = $user->first_name;
    $data['email'] = $user->email;
    $data['phone'] = $user->phone;
 
    return $data;
}
}
