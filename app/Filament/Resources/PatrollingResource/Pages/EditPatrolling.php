<?php

namespace App\Filament\Resources\PatrollingResource\Pages;

use App\Filament\Resources\PatrollingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPatrolling extends EditRecord
{
    protected static string $resource = PatrollingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Actions\DeleteAction::make(),
        ];
    }
}
