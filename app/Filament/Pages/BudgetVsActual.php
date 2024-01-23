<?php

namespace App\Filament\Pages;

use App\Filament\Resources\BudgetResource;
use App\Models\Accounting\Budget;
use App\Models\Accounting\Budgetitem;
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
    protected static ?string $title = 'Budget vs Actual';
    protected static ?string $slug = 'budget-vs-actual';

    public function table(Table $table): Table
    {   $oaId = auth()->user()->owner_association_id;
        $buildingIds = Building::where('owner_association_id',$oaId)->pluck('id');
        $budgetIds = Budget::whereIn('building_id',$buildingIds)->pluck('id');
        return $table
            ->query(Budgetitem::query()->whereIn('budget_id',$budgetIds))
            ->columns([
                TextColumn::make('budget.building.name'),
                TextColumn::make('service.code')->label('Mollak code'),
                TextColumn::make('service.name')->label('Service name'),
                ViewColumn::make('vendor')->label('Supplier Name')->view('tables.columns.service-supplier'),
                TextColumn::make('budget_excl_vat')->label('Budget Annual'),
                ViewColumn::make('actual')->label('Actual Annual')->view('tables.columns.service-actual'),
                ViewColumn::make('surplus')->label('(Deficit)/Surplus')->view('tables.columns.service-surplus'),
            ])
            ->defaultSort('created_at', 'desc')->filters([
                Filter::make('invoice_date')
                    ->form([
                        Select::make('year')
                        ->searchable()
                        ->placeholder('Select Year')
                        ->options(array_combine(range(now()->year, 2018), range(now()->year, 2018))),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['year'])) {
                                return $query->whereYear('created_at',$data['year']);
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
                        if (isset($data['building'])) {
                            $budget_ids = Budget::query()->where('building_id',$data['building'])->pluck('id')->toArray();
                            return $query->whereIn('budget_id',$budget_ids);
                                
                            }
                            return $query;
                        }),
                    ],layout: FiltersLayout::AboveContent)->filtersFormColumns(3);
    }
}
