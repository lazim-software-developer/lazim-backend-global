<?php

namespace App\Filament\Resources\ComplaintscomplaintResource\Pages;

use App\Filament\Resources\ComplaintscomplaintResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComplaintscomplaint extends EditRecord
{
    protected static string $resource = ComplaintscomplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
