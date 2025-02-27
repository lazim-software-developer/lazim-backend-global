<?php
namespace App\Filament\App\Widgets;

use App\Models\Building\Complaint;
use App\Models\Building\Flat;
use DB;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PreventiveReactiveMaintenance extends BaseWidget
{
    protected static ?string $heading          = 'Reactive Maintenance';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort                = 7;

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('No Reactive Maintenance records')
            ->query(
                Complaint::query()->whereIn('complaint_type', ['help_desk', 'tenant_complaint', 'snag'])
                    ->whereIn('flat_id', Flat::whereIn('id', function ($query) {
                        return $query->select('flat_id')
                            ->from('property_manager_flats')
                            ->where('owner_association_id', auth()->user()->owner_association_id)
                            ->where('active', true);
                    })
                            ->pluck('id'))
            )
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('Ticket Number'),
                Tables\Columns\TextColumn::make('flat.property_number')
                    ->label('Flat Number'),
                Tables\Columns\TextColumn::make('user.first_name')
                    ->label('Raised By'),
                Tables\Columns\TextColumn::make('category')
                    ->label('Category'),
                Tables\Columns\TextColumn::make('complaint')
                    ->label('Complaint')
                    ->wrap()
                    ->limit(100),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('id')
                    ->label('Maintenance History')
                    ->formatStateUsing(function ($state) {
                        $record = Complaint::find($state);

                        $openTime = $record->open_time
                        ? \Carbon\Carbon::parse($record->open_time)->format('d-m-Y H:i:s')
                        : '--';
                        $closeTime = $record->close_time
                        ? \Carbon\Carbon::parse($record->close_time)->format('d-m-Y H:i:s')
                        : '--';

                        return "Open: $openTime<br>Close: $closeTime";
                    })
                    ->html(),

            ])
            ->filters([
                Filter::make('complaint_type')
                    ->form([
                        Select::make('flat_id')
                            ->label('Flat')
                            ->native(false)
                            ->options(function () {
                                $pmFlats = DB::table('property_manager_flats')
                                    ->where('owner_association_id', auth()->user()->owner_association_id)
                                    ->where('active', true)
                                    ->pluck('flat_id')
                                    ->toArray();
                                $flats = Flat::whereIn('id', $pmFlats)
                                    ->pluck('property_number', 'id');
                                return $flats->isNotEmpty() ? $flats : ['' => 'No flats found'];
                            })
                            ->placeholder('Select Flat')
                            ->disablePlaceholderSelection(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['flat_id'],
                                fn(Builder $query): Builder => $query->where('flat_id', $data['flat_id'])
                            );
                    }),
                SelectFilter::make('month')
                    ->label('Month')
                    ->options([
                        1  => 'January',
                        2  => 'February',
                        3  => 'March',
                        4  => 'April',
                        5  => 'May',
                        6  => 'June',
                        7  => 'July',
                        8  => 'August',
                        9  => 'September',
                        10 => 'October',
                        11 => 'November',
                        12 => 'December',
                    ])
                    ->default(null)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $query, $month): Builder => $query->whereMonth('open_time', $month)
                        );
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
