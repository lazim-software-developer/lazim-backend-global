<?php

namespace App\Filament\Resources\EmailReminderResource\Pages;

use App\Filament\Resources\EmailReminderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmailReminder extends EditRecord
{
    
    protected static string $resource = EmailReminderResource::class;

    // Override the getBreadcrumbs method
    public function getBreadcrumbs(): array
    {
        return [
            '/app/mollak-tenants' => 'Dashboard',
            '/app/email-reminders' => 'Email Reminders',
        ];
    }
}
