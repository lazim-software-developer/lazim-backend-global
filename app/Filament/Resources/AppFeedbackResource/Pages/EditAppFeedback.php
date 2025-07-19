<?php

namespace App\Filament\Resources\AppFeedbackResource\Pages;

use App\Filament\Resources\AppFeedbackResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppFeedback extends EditRecord
{
    protected static string $resource = AppFeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
