<?php

namespace App\Filament\Resources\MoveInFormsDocumentResource\Pages;

use App\Filament\Resources\MoveInFormsDocumentResource;
use App\Models\Building\Document;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMoveInFormsDocument extends EditRecord
{
    protected static string $resource = MoveInFormsDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
    public function afterSave()
    {
        // If updated value of status is approved
        if($this->record->status == 'Approved') {
            Document::where('id', $this->data['id'])
                ->update([
                    'accepted_by' => auth()->id(),
                ]);
        }
    }
}
