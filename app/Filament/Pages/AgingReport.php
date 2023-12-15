<?php

namespace App\Filament\Pages;

use App\Models\Accounting\OAMInvoice;
use App\Models\Accounting\OAMReceipts;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;

class AgingReport extends Page implements HasTable
{
    use InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.aging-report';

    protected static ?string $slug = 'aging-report';

    function checkDueDate($flat,$year)
    {
        
        $quarters = ["01-Jan-$year To 31-Mar-$year","01-Apr-$year To 30-Jun-$year","01-Jul-$year To 30-Sep-$year","01-Oct-$year To 31-Dec-$year"];
        foreach($quarters as $quarter){
            $invoiceDate =OAMInvoice::where(['flat_id' => $flat->id, 'invoice_period' => $quarter])->first()?->invoice_due_date;
            $receiptDate =OAMReceipts::where(['flat_id' => $flat->id, 'receipt_period' => $quarter])->first()?->receipt_date;
            if ($invoiceDate && $receiptDate && Carbon::parse($receiptDate)->greaterThan(Carbon::parse($invoiceDate))) {
                return true;
            }
        }
        return false;
    }

    public function table(Table $table): Table
    {   $oaId = auth()->user()->owner_association_id;
        $buildingIds = Building::where('owner_association_id',$oaId)->pluck('id');
        //Get current date
        $currentDate = Carbon::now();
        //Get current year
        $currentYear = Carbon::now()->year;

        $flats = Flat::whereIn('building_id', $buildingIds)->with('oaminvoices')->get();
        $filteredFlats=$flats->filter(function ($flat) use ($currentYear, $currentDate, $buildingIds) 
            {
                    $yearlyInvoices = OAMInvoice::query()->where('invoice_period', 'like', '%' . $currentYear . '%')
                                                            ->where('flat_id' , $flat->id)
                                                            ->where('invoice_date', '<', $currentDate)
                                                            ->whereIn('building_id', $buildingIds)->sum('invoice_amount');
                    
                    $yearlyReceipts = OAMReceipts::where('flat_id' , $flat->id)->where('receipt_period', 'like', '%' . $currentYear . '%')->whereIn('building_id', $buildingIds)->sum('receipt_amount');
                    
                    if ((int)$yearlyInvoices - (int)$yearlyReceipts >0 || $this->checkDueDate($flat,$currentYear)) {
                        Log::info('flat'. $flat);
                        Log::info('invoice'. (int)$yearlyInvoices);
                        Log::info('recipts'.(int)$yearlyReceipts);
                        return $flat;
                    } else {
                        Log::info('invoice'. (int)$yearlyInvoices);
                        Log::info('recipts'.(int)$yearlyReceipts);
                    }
            })->pluck('id');
        return $table
            ->query(Flat::query()->whereIn('id', $filteredFlats)->orderBy('id'))
            ->columns([
                TextColumn::make('property_number')->label('Unit'),
                TextColumn::make('owners.name')->label('Owner'),
                // ViewColumn::make('owner')->label('Owner')->view('tables.columns.aging-report.owner-name'),
                // ViewColumn::make('service_name')->label('Service Name')->view('tables.columns.service-name'),
                // ViewColumn::make('vendor')->label('Supplier Name')->view('tables.columns.service-supplier'),
                // ViewColumn::make('budget')->label('Budget Annual')->view('tables.columns.service-budget'),
                // ViewColumn::make('actual')->label('Actual Annual')->view('tables.columns.service-actual'),
                // ViewColumn::make('surplus')->label('(Deficit)/Surplus')->view('tables.columns.service-surplus'),
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

