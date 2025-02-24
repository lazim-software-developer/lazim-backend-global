<?php

namespace App\Filament\Resources\FitOutFormsDocumentResource\Pages;

use App\Filament\Resources\FitOutFormsDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFitOutFormsDocument extends ViewRecord
{
    protected static string $resource = FitOutFormsDocumentResource::class;
    protected static ?string $title = 'FitOut';
}
