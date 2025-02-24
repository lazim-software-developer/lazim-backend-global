<?php
namespace App\Filament\Pages;

use App\Models\Building\Building;
use App\Models\Master\Role;
use DB;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected function isPropertyManager(): bool
    {
        return auth()->user()->role->name =='Property Manager';
    }

    public function filtersForm(Form $form): Form
    {
        if ($this->isPropertyManager()) {
            return $form->schema([]);
        }

        return $form
            ->schema([
                Section::make('Filters')
                    ->schema([
                        Select::make('building')
                            ->label('Select Building')
                            ->options(function () {
                                if (Role::where('id', auth()->user()->role->id)->first()->name == 'Property Manager') {
                                    return Building::whereIn('id',
                                        DB::table('building_owner_association')
                                            ->where('owner_association_id', auth()->user()->owner_association_id)
                                            ->where('active', true)
                                            ->pluck('building_id')
                                    )->pluck('name', 'id');
                                }return Building::where('owner_association_id', auth()->user()->owner_association_id)
                                    ->pluck('name', 'id');
                            })
                            ->searchable(),
                        DatePicker::make('startDate')
                            ->reactive()
                            ->label('Start Date')
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('endDate', null); // Clear the end date when the start date changes
                            }),
                        DatePicker::make('endDate')
                            ->label('End Date')
                            ->reactive()
                            ->minDate(fn(callable $get) => $get('startDate'))
                            ->maxDate(now()),
                    ])
                    ->columns(3), // Adjust the layout to accommodate three columns
            ]);
    }

    protected function getActions(): array
    {
        if ($this->isPropertyManager()) {
            return [];
        }

        return [
            Action::make('Accounting Module')
                ->label('Accounting Module')
                ->url(env('ACCOUNTING_URL')),
            Action::make('resetFilters')
                ->label('Reset Filters')
                ->color('danger')
                ->action(fn() => $this->resetFilters()),
        ];
    }

    public function resetFilters()
    {
        // $this->filters = [];
        $this->filters['building']  = null;
        $this->filters['startDate'] = null;
        $this->filters['endDate']   = null;

        session()->forget('filters');
        // $this->redirect('/admin');
        // $this->dispatchBrowserEvent('filters-reset');

    }

    public function mount()
    {
        $this->resetFilters();
    }
}
