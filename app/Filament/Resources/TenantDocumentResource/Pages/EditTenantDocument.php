<?php

namespace App\Filament\Resources\TenantDocumentResource\Pages;

use App\Filament\Resources\TenantDocumentResource;
use App\Models\Building\Document;
use App\Models\User\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditTenantDocument extends EditRecord
{
    protected static string $resource = TenantDocumentResource::class;

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
