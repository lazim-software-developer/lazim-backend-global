<?php

namespace App\Filament\Resources\AppFeedbackResource\Pages;

use App\Filament\Resources\AppFeedbackResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppFeedback extends ListRecords
{
    protected static string $resource = AppFeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
