<?php

namespace App\Filament\Resources\Master\ServiceResource\Pages;

use App\Filament\Resources\Master\ServiceResource;
use App\Models\Master\Service;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;
    protected function afterCreate()
    {
        Service::where('id', $this->record->id)
            ->update([
                'active' => true,
                'custom' => 0
            ]);
    }
    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn() => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }
}
