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
            backButton(url: url()->previous())->visible(fn() => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
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
