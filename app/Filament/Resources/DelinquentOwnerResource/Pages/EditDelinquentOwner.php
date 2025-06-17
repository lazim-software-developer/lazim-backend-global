<?php

namespace App\Filament\Resources\DelinquentOwnerResource\Pages;

use App\Filament\Resources\DelinquentOwnerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDelinquentOwner extends EditRecord
{
    protected static string $resource = DelinquentOwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Actions\DeleteAction::make(),
        ];
    }
}
