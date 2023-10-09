<?php

namespace App\Filament\Resources\BuildingDocumentResource\Pages;

use App\Filament\Resources\BuildingDocumentResource;
use App\Models\Building\Document;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateBuildingDocument extends CreateRecord
{
    protected static string $resource = BuildingDocumentResource::class;
    public function afterCreate()
    {


            $type = $this->data['documentable_type'];
            $id   = $this->data['documentable_id'];

            Document::where('id', $this->record->id)
                ->update([
                    'documentable_type' => $type,
                    'documentable_id'   => $id,
                ]);

        $user = Filament::auth()->id();
        Document::where('id', $this->record->id)
            ->update([
                'accepted_by' => $user,
            ]);




    }
}
