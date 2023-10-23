<?php

namespace App\Filament\Resources\Visitor\FlatDomesticHelpResource\Pages;

use App\Filament\Resources\Visitor\FlatDomesticHelpResource;
use App\Models\Visitor\FlatDomesticHelp;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateFlatDomesticHelp extends CreateRecord
{
    protected static string $resource = FlatDomesticHelpResource::class;
   
}
