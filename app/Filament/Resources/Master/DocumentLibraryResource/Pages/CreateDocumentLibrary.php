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
    protected function afterCreate(){
        $tenant=Filament::getTenant();
        DocumentLibrary::where('id', $this->record->id)
            ->update([
                'building_id'=>$tenant->first()->id
            ]);

    }
}
