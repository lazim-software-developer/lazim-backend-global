<?php

namespace App\Filament\Resources\EmailReminderTracking\Pages;

use App\Filament\Resources\EmailReminderHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmailReminderTracking extends ListRecords
{
    protected static string $resource = EmailReminderHistoryResource::class;

    
}
