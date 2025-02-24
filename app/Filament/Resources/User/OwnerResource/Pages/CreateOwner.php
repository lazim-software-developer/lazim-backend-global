<?php

namespace App\Filament\Resources\User\OwnerResource\Pages;

use App\Filament\Resources\User\OwnerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOwner extends CreateRecord
{
    protected static string $resource = OwnerResource::class;
}
