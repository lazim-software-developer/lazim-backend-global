<?php

namespace App\Filament\Resources\MDResource\Pages;

use App\Filament\Resources\MDResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMD extends EditRecord
{
    protected static string $resource = MDResource::class;
    protected static ?string $title = 'MD';
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
