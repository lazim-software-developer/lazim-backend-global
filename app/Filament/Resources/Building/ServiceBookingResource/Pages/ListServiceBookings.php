<?php

namespace App\Filament\Resources\Building\ServiceBookingResource\Pages;

use App\Filament\Resources\Building\ServiceBookingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListServiceBookings extends ListRecords
{
    protected static string $resource = ServiceBookingResource::class;
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('bookable_type','App\Models\Master\Service')->where('owner_association_id',auth()->user()->owner_association_id);
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
