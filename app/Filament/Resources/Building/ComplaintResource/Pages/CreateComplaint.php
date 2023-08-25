<?php

namespace App\Filament\Resources\Building\ComplaintResource\Pages;

use App\Filament\Resources\Building\ComplaintResource;
use App\Models\Building\Complaint;
use App\Models\User\User;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateComplaint extends CreateRecord
{
    protected static string $resource = ComplaintResource::class;
    protected function afterCreate(){
        $tenant=Filament::getTenant();
        $jsonValue = json_encode(['comment' => $this->record->remarks,'date'=>now(),
        'user'=> User::where('id',$this->record->user_id)->first()->first_name
    ]);

        Complaint::where('id', $this->record->id)
            ->update([

                'remarks' => $jsonValue
            ]);

        Complaint::where('id', $this->record->id)
            ->update([
                'building_id'=>$tenant->first()->id
            ]);
    }



}
