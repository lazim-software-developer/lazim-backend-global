<?php

namespace App\Filament\Resources\AllUsersResource\Pages;

use App\Filament\Resources\AllUsersResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAllUsers extends CreateRecord
{
    protected static string $resource = AllUsersResource::class;
}
