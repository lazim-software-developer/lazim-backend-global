<?php

namespace App\Filament\Resources\Building\DocumentsResource\Pages;

use App\Filament\Resources\Building\DocumentsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
