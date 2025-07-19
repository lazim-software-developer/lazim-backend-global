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
            Actions\DeleteAction::make(),
        ];
    }
}
