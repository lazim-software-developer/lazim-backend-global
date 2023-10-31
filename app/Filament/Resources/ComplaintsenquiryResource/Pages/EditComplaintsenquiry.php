<?php

namespace App\Filament\Resources\ComplaintsenquiryResource\Pages;

use App\Filament\Resources\ComplaintsenquiryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComplaintsenquiry extends EditRecord
{
    protected static string $resource = ComplaintsenquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
