<?php

namespace App\Filament\Resources\TenantComplaints\TenantcomplaintResource\Pages;

use App\Filament\Resources\TenantComplaints\TenantcomplaintResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTenantcomplaints extends ListRecords
{
    protected static string $resource = TenantcomplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Actions\CreateAction::make(),
        ];
    }
}
