<?php

namespace App\Filament\Resources\User\OwnerResource\Pages;

use Filament\Actions;
use App\Models\FlatOwners;
use App\Models\Building\Flat;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\User\OwnerResource;

class ViewOwner extends ViewRecord
{
    protected static string $resource = OwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }

    public function beforeFill(): void
    {
        $Assignnflats = FlatOwners::where('owner_id', $this->record->id)->get();
        foreach ($Assignnflats as $flat_value) {
            $flatDetail = Flat::where('id', $flat_value->flat_id)->first();
        }
        if (empty($this->record->building_id)) {
            $this->record->building_id = $flatDetail->building_id;
        }
        $this->record->save();
    }
}
