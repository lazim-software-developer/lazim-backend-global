<?php

namespace App\Filament\Resources\ComplaintssuggessionResource\Pages;

use App\Filament\Resources\ComplaintssuggessionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComplaintssuggession extends EditRecord
{
    protected static string $resource = ComplaintssuggessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
