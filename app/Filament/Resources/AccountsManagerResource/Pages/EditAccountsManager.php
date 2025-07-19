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
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
