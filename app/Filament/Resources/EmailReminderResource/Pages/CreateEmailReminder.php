<?php

namespace App\Filament\Resources\EmailReminderResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\EmailReminderResource;

class CreateEmailReminder extends CreateRecord
{
    protected static string $resource = EmailReminderResource::class;
}
