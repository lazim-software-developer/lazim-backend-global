<?php

namespace App\Filament\Resources\SnagsResource\Pages;

use App\Filament\Resources\SnagsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSnags extends CreateRecord
{
    protected static string $resource = SnagsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn() => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
