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
            '/admin/dashboard' => 'Dashboard',
            '/admin/email-reminders' => 'Email Reminders',
        ];
    }
}
