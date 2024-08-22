<?php
namespace App\Filament\Pages;

use App\Models\Building\Building;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;

class Dashboard extends BaseDashboard
{
    use HasFiltersAction;

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->form([
                    Select::make('building_id')
                        ->label('Select Building')
                        ->options(Building::all()->pluck('name', 'id')) // Assuming Building model has `name` and `id`
                        ->searchable()
                        ->placeholder('Select a building'),
                ]),
        ];
    }
}
