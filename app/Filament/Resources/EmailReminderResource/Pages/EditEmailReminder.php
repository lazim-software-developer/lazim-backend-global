<?php

namespace App\Filament\Resources\EmailReminderResource\Pages;

use App\Filament\Resources\EmailReminderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmailReminder extends EditRecord
{

    protected static string $resource = EmailReminderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }

    // Override the getBreadcrumbs method
    public function getBreadcrumbs(): array
    {
        return [
            '/app/mollak-tenants' => 'Dashboard',
            '/app/email-reminders' => 'Email Reminders',
        ];
    }
}
