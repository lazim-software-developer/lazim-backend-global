<?php

namespace App\Filament\Resources\User\UserResource\Pages;
use App\Filament\Forms\Components\Component;

use App\Filament\Resources\User\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
