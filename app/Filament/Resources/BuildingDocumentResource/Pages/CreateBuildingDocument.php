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
    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }
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
