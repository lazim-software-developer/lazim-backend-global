<?php

namespace App\Filament\Resources\Vendor\ContactsResource\Pages;

use App\Filament\Resources\Vendor\ContactsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateContacts extends CreateRecord
{
    protected static string $resource = ContactsResource::class;
}
