<?php

namespace App\Filament\Resources\FlatDocumentResource\Pages;

use App\Filament\Resources\FlatDocumentResource;
use App\Models\Building\Document;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateFlatDocument extends CreateRecord
{
    protected static string $resource = FlatDocumentResource::class;
     protected function afterCreate()
    {
        $user = Filament::auth()->id();
        Document::where('id', $this->record->id)
            ->update([
                'accepted_by' => $user,
            ]);

        $type = $this->data['documentable_type'];
        $id   = $this->data['documentable_id'];

        Document::where('id', $this->record->id)
            ->update([
                'documentable_type' => $type,
                'documentable_id'   => $id,
            ]);

    }
}
