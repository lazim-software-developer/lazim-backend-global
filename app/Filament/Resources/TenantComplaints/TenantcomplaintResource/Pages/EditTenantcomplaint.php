<?php

namespace App\Filament\Resources\TenantComplaints\TenantcomplaintResource\Pages;

use App\Filament\Resources\TenantComplaints\TenantcomplaintResource;
use App\Models\Building\Complaint;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTenantcomplaint extends EditRecord
{
    protected static string $resource = TenantcomplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Actions\DeleteAction::make(),
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
