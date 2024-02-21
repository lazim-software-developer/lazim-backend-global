<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatrollingResource\Pages;
use App\Filament\Resources\PatrollingResource\RelationManagers;
use App\Models\Building\Building;
use App\Models\Gatekeeper\Patrolling;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PatrollingResource extends Resource
{
    protected static ?string $model = Patrolling::class;

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
        $buildings = Building::where('owner_association_id',auth()->user()->owner_association_id)->pluck('id');
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('building_id', $buildings)->withoutGlobalScopes())
            ->columns([
                TextColumn::make('building.name'),
                TextColumn::make('floor.floors'),
                TextColumn::make('user.first_name')->label('Patrolled By'),
                TextColumn::make('patrolled_at'),

            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
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
