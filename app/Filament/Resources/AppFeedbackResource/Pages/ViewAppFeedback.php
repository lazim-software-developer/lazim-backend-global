<?php

namespace App\Filament\Resources\AppFeedbackResource\Pages;

use App\Filament\Resources\AppFeedbackResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAppFeedback extends ViewRecord
{
    protected static string $resource = AppFeedbackResource::class;
        protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }
}
