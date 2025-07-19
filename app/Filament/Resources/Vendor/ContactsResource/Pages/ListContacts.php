<?php

namespace App\Filament\Resources\Vendor\ContactsResource\Pages;

use App\Filament\Resources\Vendor\ContactsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContacts extends ListRecords
{
    protected static string $resource = ContactsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
