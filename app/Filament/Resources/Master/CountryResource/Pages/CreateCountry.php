<?php

namespace App\Filament\Resources\Master\CountryResource\Pages;

use App\Filament\Resources\Master\CountryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCountry extends CreateRecord
{
    protected static string $resource = CountryResource::class;
}
