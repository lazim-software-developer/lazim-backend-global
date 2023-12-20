<?php

namespace App\Filament\Resources\Building\ComplaintResource\Pages;

use App\Filament\Resources\Building\ComplaintResource;
use App\Models\Building\Complaint;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComplaint extends EditRecord
{
    protected static string $resource = ComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
    protected function afterSave()
    {
        $status = $this->record->status;
        if ($status == 'completed') {
            Complaint::where('id', $this->record->id)
                ->update([
                    'close_time' => now(),
                ]);
        }

    }
}
