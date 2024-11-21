<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoolingAccountResource\Pages;
use App\Models\Building\Building;
use App\Models\CoolingAccount;
use App\Models\Master\Role;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use DB;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class CoolingAccountResource extends Resource
{
    protected static ?string $model = CoolingAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

   public static function table(Table $table): Table
    {
        CoolingAccount::where('status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);

        return $table
        // ->modifyQueryUsing(function(Builder $query){
        //     $userRole = auth()->user()->role->name ?? null;

        //     if($userRole == 'Property Manager'){
        //         $buildingIds = DB::table('building_owner_association')
        //             ->where('owner_association_id', auth()->user()->owner_association_id)
        //             ->pluck('building_id')
        //             ->toArray();

        //         if (!empty($buildingIds)) {
        //             $query->whereIn('building_id', $buildingIds);
        //         }
        //     }
        // })
            ->columns([
                TextColumn::make('building.name'),
                TextColumn::make('flat.property_number')->label('Unit number'),
                TextColumn::make('date')->date(),
                TextColumn::make('due_date')->date()
                ->visible(function(){
                    if(auth()->user()->role->name == 'Property Manager'){
                        return true;
                    }
                }),
                TextColumn::make('opening_balance'),
                TextColumn::make('consumption'),
                TextColumn::make('demand_charge'),
                TextColumn::make('security_deposit'),
                TextColumn::make('billing_charges'),
                TextColumn::make('other_charges'),
                TextColumn::make('receipts'),
                TextColumn::make('closing_balance'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'overdue' => 'danger',
                        'pending' => 'warning',
                    })
                    ->visible(function(){
                            if(auth()->user()->role->name == 'Property Manager'){
                                return true;
                            }
                        }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('Date')
                    ->form([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('from')
                                    ->label('From')
                                    ->placeholder('Select start date'),
                                DatePicker::make('to')
                                    ->label('To')
                                    ->placeholder('Select end date')
                                    ->after('from'),
                            ]),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if ($data['from'] && $data['to']) {
                            return "From {$data['from']} to {$data['to']}";
                        }
                        if ($data['from']) {
                            return "From {$data['from']}";
                        }
                        if ($data['to']) {
                            return "Until {$data['to']}";
                        }
                        return null;
                    })
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], fn($query) => $query->whereDate('date', '>=', $data['from']))
                            ->when($data['to'], fn($query) => $query->whereDate('date', '<=', $data['to']));
                    }),
                Filter::make('Building')
                    ->form([
                        Select::make('building')
                            ->searchable()
                            ->preload()
                            ->placeholder('Select building')
                            ->options(function () {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return Building::all()->pluck('name', 'id');
                                } elseif (Role::where('id', auth()->user()->role->id)->first()->name == 'Property Manager') {
                                    return Building::whereIn('id',
                                        DB::table('building_owner_association')
                                            ->where('owner_association_id', auth()->user()->owner_association_id)
                                            ->where('active', true)
                                            ->pluck('building_id')
                                    )->pluck('name', 'id');
                                }
                                return Building::where('owner_association_id', auth()->user()?->owner_association_id)
                                    ->pluck('name', 'id');
                            }),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['building'],
                            fn(Builder $query, $building_id): Builder => $query->where('building_id', $building_id),
                        );
                    }),
                Filter::make('status')
                    ->form([
                        Select::make('status')
                            ->placeholder('Select status')
                            ->options([
                                'paid' => 'Paid',
                                'overdue' => 'Overdue',
                                'pending' => 'Pending',
                            ])
                            ->preload(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['status'],
                            fn(Builder $query) => $query->where('status', $data['status'])
                        );
                    }),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(3)
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoolingAccounts::route('/'),
            // 'create' => Pages\CreateCoolingAccount::route('/create'),
            // 'view' => Pages\ViewCoolingAccount::route('/{record}'),
            // 'edit' => Pages\EditCoolingAccount::route('/{record}/edit'),
        ];
    }
}
