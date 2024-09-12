<?php

namespace App\Filament\Resources\VehicleResource\Pages;

use App\Filament\Resources\VehicleResource;
use App\Models\Building\Flat;
use App\Models\Vehicle;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateVehicle extends CreateRecord
{
    protected static string $resource = VehicleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $flat = Flat::find($data['flat_id']);
        $vehicleCount = Vehicle::where('flat_id',$data['flat_id'])->get()->count();

        $data['parking_number']=$flat?->property_number.'-'.$data['parking_number'];

        if ($vehicleCount > $flat->parking_count) {
            Notification::make()
                ->warning()
                ->title('No Slots!')
                ->body('No Available parking slot for this flat.')
                ->send();
        
            $this->halt();
        }
        elseif(Vehicle::where('parking_number',$data['parking_number'])->exists()){
            Notification::make()
                ->warning()
                ->title('Dulpicate')
                ->body('This parking no. already exists.')
                ->send();
        
            $this->halt();
        }

        return $data;
    }
}
