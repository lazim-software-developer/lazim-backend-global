<?php

namespace App\Filament\Resources\UserApprovalResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\UserApprovalResource;

class CreateUserApproval extends CreateRecord
{
    protected static string $resource = UserApprovalResource::class;
}
