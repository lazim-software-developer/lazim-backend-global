<?php

namespace App\Filament\Resources\SnaggingResource\Pages;

use App\Filament\Resources\SnaggingResource;
use App\Models\Building\Complaint;
use App\Models\User\User;
use Doctrine\DBAL\Types\JsonType;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSnagging extends CreateRecord
{
    protected static string $resource = SnaggingResource::class;
    protected function afterCreate(){
        $jsonValue = json_encode(['comment' => $this->record->remarks,'date'=>now(),
        'user'=> User::where('id',$this->record->user_id)->first()->first_name
    ]);

        Complaint::where('id', $this->record->id)
            ->update([
                'remarks' => $jsonValue
            ]);
    }
}
