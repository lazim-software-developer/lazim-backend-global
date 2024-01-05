<?php

namespace App\Filament\Resources\Building\BuildingResource\Pages;

use Filament\Tables\Table;
use App\Models\Master\Service;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Filament\Resources\Building\BuildingResource;

class ShowServices extends Page implements HasTable
{
    use InteractsWithTable;
    protected static string $resource = BuildingResource::class;

    protected static ?string $title = 'Inhouse service';
    protected static string $view = 'filament.resources.building.building-resource.pages.show-services';

    public function table(Table $table): Table
    {   
        return $table
            ->query(Service::query()->where('type','inhouse'))
            ->columns([
                TextColumn::make('name')->default('NA'),
                TextColumn::make('price')->default('NA'),
                IconColumn::make('active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),
                TextColumn::make('payment_link')->default('NA'),
               
            ])
            ->defaultSort('created_at', 'desc');
    }
}
