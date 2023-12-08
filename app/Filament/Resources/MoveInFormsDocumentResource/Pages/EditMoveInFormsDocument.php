<?php

namespace App\Filament\Resources\MoveInFormsDocumentResource\Pages;

use App\Filament\Resources\MoveInFormsDocumentResource;
use App\Models\Building\Document;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

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
        if ($this->record->status == 'Approved') {
            Document::where('id', $this->data['id'])
                ->update([
                    'accepted_by' => auth()->id(),
                ]);
        }

        $selectedCheckboxes = $this->form->getState('rejected_fields');

        $jsonRejectedFields = json_encode($selectedCheckboxes);

        $this->record->rejected_fields = $jsonRejectedFields;
        $this->record->save();
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
