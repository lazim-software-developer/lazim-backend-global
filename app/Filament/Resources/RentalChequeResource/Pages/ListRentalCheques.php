<?php

namespace App\Filament\Resources\RentalChequeResource\Pages;

use App\Filament\Resources\RentalChequeResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListRentalCheques extends ListRecords
{
    protected static string $resource = RentalChequeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Upcoming' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Upcoming')),
            'Paid'     => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Paid')),
            'Overdue'  => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Overdue')),
        ];
    }
}
