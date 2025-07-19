<?php

namespace App\Filament\Resources\WDAResource\Pages;

use App\Filament\Resources\WDAResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWDA extends EditRecord
{
    protected static string $resource = WDAResource::class;
    protected static ?string $title = 'WDA';

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\DeleteAction::make(),
        ];
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($data['status'] == 'pending') {
            $data['status'] = null;
        }
        return $data;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
