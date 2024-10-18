<?php

namespace App\Filament\Resources\FacilitySupportComplaintResource\Pages;

use App\Filament\Resources\FacilitySupportComplaintResource;
use App\Models\Building\Complaint;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFacilitySupportComplaint extends EditRecord
{
    protected static string $resource = FacilitySupportComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function afterSave()
    {
        if ($this->record->status == 'closed') {
            Complaint::where('id', $this->data['id'])
                ->update([
                    'closed_by'  => auth()->user()->id,
                    'close_time' => Carbon::now(),
                ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
