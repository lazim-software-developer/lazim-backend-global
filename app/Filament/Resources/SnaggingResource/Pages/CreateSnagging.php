<?php

namespace App\Filament\Resources\SnaggingResource\Pages;

use App\Filament\Resources\SnaggingResource;
use App\Models\Building\Complaint;
use Doctrine\DBAL\Types\JsonType;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSnagging extends CreateRecord
{
    protected static string $resource = SnaggingResource::class;
    // protected function afterCreate(){
    //     $jsonValue =JsonType([]);
    //     Complaint::where('id',$this->record->id)
    //             ->update([
    //                 "remarks"=>[{
    //                     "comment":$this->record->remark}
    //                     ,"date":now();
    //                     ,"user":user->name;}]

    //             ]);
    // }
}
