<?php
namespace App\Filament\Pages;

use App\Models\Building\Building;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Actions\Modal\Actions\ButtonAction;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;
 
    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filters')
                    ->schema([
                        Select::make('building')
                            ->label('Select Building')
                            ->options(Building::where('owner_association_id', auth()->user()->owner_association_id)
                                ->pluck('name', 'id')) // Fetch building names and IDs
                            ->searchable(),
                        DatePicker::make('startDate')
                            ->label('Start Date'),
                        DatePicker::make('endDate')
                            ->label('End Date'),
                    ])
                    ->columns(3), // Adjust the layout to accommodate three columns
            ]);
    }

    protected function getActions(): array
    {
        return [
            Action::make('resetFilters')
                ->label('Reset Filters')
                ->color('danger')
                ->action(fn () => $this->resetFilters()),
        ];
    }

    public function resetFilters()
    {
        $this->filters = []; // Reset the filters to an empty array
        // $this->redirect($this->getUrl()); 

    }

}
