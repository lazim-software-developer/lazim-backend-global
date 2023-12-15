<?php

namespace App\Filament\Resources\User\TenantResource\Pages;

use App\Filament\Resources\User\TenantResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;
}
