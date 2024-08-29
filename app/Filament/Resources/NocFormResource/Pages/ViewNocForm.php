<?php

namespace App\Filament\Resources\NocFormResource\Pages;

use App\Filament\Resources\NocFormResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewNocForm extends ViewRecord
{
    protected static string $resource = NocFormResource::class;
    protected static ?string $title = 'Sale NOC';
}
