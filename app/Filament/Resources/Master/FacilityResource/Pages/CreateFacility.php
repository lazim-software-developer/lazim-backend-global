<?php

namespace App\Filament\Resources\Master\FacilityResource\Pages;

use App\Filament\Resources\Master\FacilityResource;
use App\Models\Master\Facility;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateFacility extends CreateRecord
{
    protected static string $resource = FacilityResource::class;

    public function getTitle(): string
    {
        return 'Create Amenity';
    }

    protected function afterCreate(){

        Facility::where('id', $this->record->id)
            ->update([
                'active'=>1
            ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }

}
