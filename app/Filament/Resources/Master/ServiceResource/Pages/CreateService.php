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
    protected function afterCreate(){
        Service::where('id',$this->record->id)
                ->update([
                    'active'=>true,
                ]);


    }
}
