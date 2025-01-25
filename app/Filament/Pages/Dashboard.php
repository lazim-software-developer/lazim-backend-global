<?php

namespace App\Filament\Pages;

use App\Models\Building\Building;
use App\Services\AuthenticationService;
use App\Services\GenericHttpService;
use App\Services\SessionCryptoService;
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
                            ->reactive()
                            ->label('Start Date')
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('endDate', null); // Clear the end date when the start date changes
                            }),
                        DatePicker::make('endDate')
                            ->label('End Date')
                            ->reactive()
                            ->minDate(fn(callable $get) => $get('startDate'))
                            ->maxDate(now())
                    ])
                    ->columns(3), // Adjust the layout to accommodate three columns
            ]);
    }

    protected function getActions(): array
    {
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
        $this->filters['building'] = null;
        $this->filters['startDate'] = null;
        $this->filters['endDate'] = null;

        session()->forget('filters');
        // $this->redirect('/admin'); 
        // $this->dispatchBrowserEvent('filters-reset');

    }

    public function mount()
    {
        // $token = SessionCryptoService::get("API_TOKEN_KEY");
        // dd($token);
        // $resposne = GenericHttpService::get("/owner-associations"); // api loggedin
        // dd($resposne);
        $this->resetFilters();
    }
}
