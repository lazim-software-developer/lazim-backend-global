<?php

namespace App\Filament\Resources\AccountsManagerResource\Pages;

use App\Filament\Resources\AccountsManagerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountsManager extends EditRecord
{
    protected static string $resource = AccountsManagerResource::class;
    protected static ?string $title = 'Accounts Manager';
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
