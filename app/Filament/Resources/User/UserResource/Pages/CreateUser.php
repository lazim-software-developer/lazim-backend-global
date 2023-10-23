<?php

namespace App\Filament\Resources\User\UserResource\Pages;
use App\Filament\Forms\Components\Component;

use App\Filament\Resources\User\UserResource;
use App\Models\User\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    protected function afterCreate()
    {
        User::where('id',$this->record->id)
        ->update([
            'active' => 1
        ]);
    }
}
