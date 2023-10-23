<?php

namespace App\Filament\Resources\Master\DocumentLibraryResource\Pages;

use App\Filament\Resources\Master\DocumentLibraryResource;
use App\Models\Master\DocumentLibrary;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateDocumentLibrary extends CreateRecord
{
    protected static string $resource = DocumentLibraryResource::class;

}
