<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBills extends ListRecords
{
    protected static string $resource = BillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Bill'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'BTU'               => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'BTU')),
            'DEWA'              => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'DEWA')),
            'Telecommunication' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'Telecommunication')),
            'lpg'               => Tab::make()
                ->label('LPG')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'lpg')),
        ];
    }
}
