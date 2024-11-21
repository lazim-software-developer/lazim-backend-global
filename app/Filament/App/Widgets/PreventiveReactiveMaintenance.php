<?php

namespace App\Filament\App\Widgets;

use App\Models\Building\Complaint;
use App\Models\Building\Flat;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PreventiveReactiveMaintenance extends BaseWidget
{
    protected static ?string $heading          = 'Reactive Maintenance';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Complaint::query()->whereIn('complaint_type', ['help_desk', 'tenant_complaint', 'snag'])
                    ->whereIn('flat_id', Flat::where('owner_association_id', auth()->user()->owner_association_id)->pluck('id'))
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

                        $openTime  = $record->open_time
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
                            ->options(Flat::where('owner_association_id', auth()->user()->owner_association_id)
                                    ->pluck('property_number', 'id')
                            )
                            ->placeholder('Select Flat'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['flat_id'],
                                fn(Builder $query): Builder => $query->where('flat_id', $data['flat_id'])
                            );
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
