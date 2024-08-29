<?php

namespace App\Filament\Resources\AccountsManagerResource\Pages;

use App\Filament\Resources\AccountsManagerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAccountsManagers extends ViewRecord
{
    protected static string $resource = AccountsManagerResource::class;
    protected static ?string $title = 'Accounts Manager';
}
