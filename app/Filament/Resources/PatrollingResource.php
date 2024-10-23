<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatrollingResource\Pages;
use App\Filament\Resources\PatrollingResource\RelationManagers;
use App\Models\Building\Building;
use App\Models\Floor;
use App\Models\Gatekeeper\Patrolling;
use App\Models\Master\Role;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class PatrollingResource extends Resource
{
    protected static ?string $model = Patrolling::class;
    protected static ?string $modelLabel      = 'Patrollings';

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
        $buildings = Building::where('owner_association_id',auth()->user()?->owner_association_id)->pluck('id');
        return $table
            // ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('building_id', $buildings)->orderBy('patrolled_at','desc')->withoutGlobalScopes())
            ->columns([
                TextColumn::make('building.name'),
                TextColumn::make('floor.floors'),
                TextColumn::make('user.first_name')->label('Patrolled by'),
                TextColumn::make('patrolled_at'),

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
                // Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatrollings::route('/'),
            // 'create' => Pages\CreatePatrolling::route('/create'),
            // 'edit' => Pages\EditPatrolling::route('/{record}/edit'),
        ];
    }
}
