<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
// use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PatrollingResource\Pages;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\PatrollingResource\RelationManagers;
use App\Filament\Resources\PatrollingResource\RelationManagers\PatrollingListRelationManager;

class PatrollingResource extends Resource
{
    protected static ?string $model = Patrolling::class;
    protected static ?string $modelLabel      = 'Patrollings';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    // public static function infolist(Infolist $infolist): Infolist
    // {
    //     return $infolist
    //         ->schema([
    //             Infolists\Components\TextEntry::make('building_id'),
    //             Infolists\Components\TextEntry::make('patrolled_by'),
    //             Infolists\Components\TextEntry::make('started_at'),
    //             Infolists\Components\TextEntry::make('ended_at'),
    //             Infolists\Components\TextEntry::make('total_count'),
    //             Infolists\Components\TextEntry::make('completed_count'),
    //             Infolists\Components\TextEntry::make('pending_count'),
    //         ]);
    // }

    public static function table(Table $table): Table
    {
        // $buildings = Building::where('owner_association_id', auth()->user()?->owner_association_id)->pluck('id');
        return $table
            // ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('building_id', $buildings)->orderBy('patrolled_at','desc')->withoutGlobalScopes())
            ->columns([
                TextColumn::make('building.name')->label('Building'),
                TextColumn::make('started_at')->label('Started At'),
                TextColumn::make('ended_at')->label('Ended At'),
                TextColumn::make('total_count')->label('Total'),
                TextColumn::make('completed_count')->label('Completed'),
                TextColumn::make('pending_count')->label('Pending'),
                // TextColumn::make('is_completed')->label('Status')->formatStateUsing(fn ($state) => $state == 1 ? 'Completed' : (($state == 0) ? 'In-Progress' : 'In-complete'))
                TextColumn::make('is_completed')->label('Status')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        1 => 'Completed',
                        0 => 'In-Progress',
                        default => 'In-complete',
                    })
                    ->color(fn ($state) => match ($state) {
                        1 => 'success',
                        0 => 'warning',
                        default => 'danger',
                    })
                    ->icon(fn ($state) => match ($state) {
                        1 => 'heroicon-o-check-circle',
                        0 => 'heroicon-o-minus-circle',
                        default => 'heroicon-o-x-circle',
                    }),
            ])
            ->filters([
                Filter::make('filter')
                    ->form([
                        Select::make('building_id')
                            ->options(function () {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return Building::all()->pluck('name', 'id');
                                } else {
                                    $buildingId = DB::table('building_owner_association')->where('owner_association_id',auth()->user()?->owner_association_id)->where('active',true)->pluck('building_id');
                                    return Building::whereIn('id',$buildingId)->pluck('name', 'id');
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->label('Building')
                            ->reactive(),
                        Select::make('floor_id')
                            ->label('Floor')
                            ->options(function (callable $get) {
                                if (empty($get('building_id'))) {
                                    return [];
                                } else {
                                    return Floor::where('building_id', $get('building_id'))
                                        ->pluck('floors', 'id');
                                }
                            })
                            ->searchable(),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['building_id']) && $data['building_id']) {
                            $query->where('building_id', $data['building_id']);
                        }

                        if (isset($data['floor_id']) && $data['floor_id']) {
                            $query->where('floor_id', $data['floor_id']);
                        }
                    }),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PatrollingListRelationManager::class, // Relation manager for patrollingList
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatrollings::route('/'),
            // 'create' => Pages\CreatePatrolling::route('/create'),
            'view' => Pages\ViewPatrolling::route('/{record}'),
            // 'edit' => Pages\EditPatrolling::route('/{record}/edit'),
        ];
    }
}
