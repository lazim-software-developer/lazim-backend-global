<?php

namespace App\Filament\Pages;

use App\Filament\Resources\BudgetResource;
use App\Models\Accounting\Budget;
use App\Models\Building\Building;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BudgetVsActual extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.budget-vs-actual';

    protected static ?string $slug = 'budget-vs-actual';

    public function table(Table $table): Table
    {   $oaId = auth()->user()->owner_association_id;
        $buildingIds = Building::where('owner_association_id',$oaId)->pluck('id');
        return $table
            ->query(Budget::query()->whereIn('building_id',$buildingIds)->where('budget_to','>=',Carbon::now()->toDateString()))
            ->columns([
                TextColumn::make('building.name'),
                ViewColumn::make('service')->label('Mollak code')->view('tables.columns.service-code'),
                ViewColumn::make('service_name')->label('Service Name')->view('tables.columns.service-name'),
                ViewColumn::make('vendor')->label('Supplier Name')->view('tables.columns.service-supplier'),
                ViewColumn::make('budget')->label('Budget Annual')->view('tables.columns.service-budget'),
                ViewColumn::make('actual')->label('Actual Annual')->view('tables.columns.service-actual'),
                ViewColumn::make('surplus')->label('(Deficit)/Surplus')->view('tables.columns.service-surplus'),
            ])->filters([
                Filter::make('invoice_date')
                    ->form([
                        Select::make('year')
                        ->searchable()
                        ->placeholder('Select Year')
                        ->options(array_combine(range(now()->year, 2018), range(now()->year, 2018))),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['year'])) {       
                                return $query
                                    ->when(
                                        $data['year'],
                                        fn (Builder $query, $year) => $query->whereYear('budget_from', $year)->orWhereYear('budget_to', $year)
                                    );
                        }
                        
                            return $query;
                        }),
                Filter::make('Building')
                    ->form([
                        Select::make('building')
                        ->searchable()
                        ->options(function () {
                            $oaId = auth()->user()->owner_association_id;
                            return Building::where('owner_association_id', $oaId)
                                ->pluck('name', 'id');
                        })
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['building'],
                                fn (Builder $query, $building_id): Builder => $query->where('building_id', $building_id),
                            );
                        }),
                    ],layout: FiltersLayout::AboveContent)->filtersFormColumns(3);
    }
}
