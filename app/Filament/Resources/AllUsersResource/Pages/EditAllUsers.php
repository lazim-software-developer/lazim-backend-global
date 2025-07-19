<?php

namespace App\Filament\Resources\AllUsersResource\Pages;

use App\Filament\Resources\AllUsersResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAllUsers extends EditRecord
{
    protected static string $resource = AllUsersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), # TODO: Change this to the correct association ID or condition
            Actions\DeleteAction::make(),
        ];
    }
}
