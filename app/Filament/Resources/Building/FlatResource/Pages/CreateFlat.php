<?php

namespace App\Filament\Resources\Building\FlatResource\Pages;

use App\Filament\Resources\Building\FlatResource;
use App\Models\Building\Flat;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateFlat extends CreateRecord
{
    protected static string $resource = FlatResource::class;
   
}
