<?php

namespace App\Filament\Pages;

use App\Models\Building\FlatTenant;
use Carbon\Carbon;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ContractExpiryOverview extends Page implements HasTable
{
    use InteractsWithTable;
    use InteractsWithFormActions;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view            = 'filament.pages.contract-expiry-overview';
    protected static ?string $title          = 'Contract Expiry Overview';
    public $days                             = 100; // Default value

    public function mount(): void
    {
        $this->days = request()->query('days', 100);
    }

    public function getActiveTab(): string
    {
        return (string) $this->days; // Cast to string to ensure type consistency
    }

    public function setActiveTab(string $days): void
    {
        $this->days = $days;
        $this->redirect('/app/contract-expiry-overview?' . http_build_query(['days' => $days]));
    }

    public function getTabGroups(): array
    {
        $today     = Carbon::now();
        $baseQuery = FlatTenant::query()
            ->whereHas('building', function ($query) {
                $query->where('owner_association_id', auth()->user()->owner_association_id);
            })
            ->where('active', true);

        return [
            'all' => [
                'label' => 'All Records',
                'query' => clone $baseQuery,
            ],
            '100' => [
                'label' => 'Within 100 days',
                'query' => (clone $baseQuery)
                    ->where('end_date', '<=', $today->copy()->addDays(100))
                    ->where('end_date', '>', $today),
            ],
            '60'  => [
                'label' => 'Within 60 days',
                'query' => (clone $baseQuery)
                    ->where('end_date', '<=', $today->copy()->addDays(60))
                    ->where('end_date', '>', $today),
            ],
            '30'  => [
                'label' => 'Within 30 days',
                'query' => (clone $baseQuery)
                    ->where('end_date', '<=', $today->copy()->addDays(30))
                    ->where('end_date', '>', $today),
            ],
        ];
    }

    public function table(Table $table): Table
    {
        $tabGroups = $this->getTabGroups();
        $activeTab = $this->getActiveTab();

        return $table
            ->query($tabGroups[$activeTab]['query'])
            ->columns([
                TextColumn::make('user.first_name')
                    ->label('Resident Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('flat.property_number')
                    ->label('Unit Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('building.name')
                    ->label('Building Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Expiry Date')
                    ->date()
                    ->sortable(),
            ]);
    }
}
