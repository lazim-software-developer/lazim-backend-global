<?php

namespace App\Filament\Resources\HelpdeskcomplaintResource\Pages;

use App\Filament\Resources\HelpdeskcomplaintResource;
use App\Models\Building\Complaint;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHelpdeskcomplaint extends EditRecord
{
    protected static string $resource = HelpdeskcomplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    public function afterSave()
    {
        if($this->record->status == 'completed')
        {
            Complaint::where('id', $this->data['id'])
                ->update([
                    'closed_by'  => auth()->user()->id,
                ]);
        }
    }

}
