<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use App\Models\Gatekeeper\Patrolling;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PatrollingResource\Pages;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\PatrollingResource\RelationManagers;

class PatrollingResource extends Resource
{
    protected static ?string $model = Patrolling::class;
    protected static ?string $modelLabel = 'Patrollings';

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
        $buildings = Building::where('owner_association_id', auth()->user()?->owner_association_id)->pluck('id');
        return $table
            // ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('building_id', $buildings)->orderBy('patrolled_at','desc')->withoutGlobalScopes())
            ->columns([
                TextColumn::make('building.name')
                    // ->sortable()
                    ->label('Building'),
                TextColumn::make('floor.floors')
                    // ->sortable()
                    ->label('Floor'),
                TextColumn::make('user.first_name')->label('Patrolled By')
                    // ->sortable()
                    ->label('Patrolled By'),
                TextColumn::make('patrolled_at')
                    // ->sortable()
                    ->label('Patrolled At'),

            ])
            ->filters([
                SelectFilter::make('building_id')
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return Building::all()->pluck('name', 'id');
                        } elseif (Role::where('id', auth()->user()->role_id)->first()->name == 'Property Manager') {
                            $buildings = DB::table('building_owner_association')
                                ->where('owner_association_id', auth()->user()->owner_association_id)
                                ->where('active', true)
                                ->pluck('building_id');
                            return Building::whereIn('id', $buildings)->pluck('name', 'id');
                        } else {
                            return Building::where('owner_association_id', auth()->user()?->owner_association_id)
                                ->pluck('name', 'id');
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->label('Building'),
            ])
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
