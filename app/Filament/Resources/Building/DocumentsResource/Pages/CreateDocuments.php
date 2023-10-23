<?php

namespace App\Filament\Resources\Building\DocumentsResource\Pages;

use App\Filament\Resources\Building\DocumentsResource;
use App\Models\Building\Document;
use App\Models\User\User;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateDocuments extends CreateRecord
{
    protected static string $resource = DocumentsResource::class;
    protected function afterCreate(){
        $jsonValue = json_encode(['comments' => $this->record->remarks,'date'=>now(),

    ]);

        Document::where('id', $this->record->id)
            ->update([
                'comments' => $jsonValue
            ]);
        
    }
}
