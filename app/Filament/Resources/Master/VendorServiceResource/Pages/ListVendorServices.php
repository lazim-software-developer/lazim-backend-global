<?php

namespace App\Filament\Resources\Master\VendorServiceResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Master\VendorServiceResource;

class ListVendorServices extends ListRecords
{
    protected static string $resource = VendorServiceResource::class;
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->where(function ($query) {
                $query->where('type', '!=', 'inhouse')
                  ->orWhereNull('type');
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Actions\CreateAction::make(),
        ];
    }
}
