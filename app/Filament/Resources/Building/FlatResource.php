<?php

namespace App\Filament\Resources\Building;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\Building\Flat;
use Filament\Facades\Filament;
use App\Models\OwnerAssociation;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Building\FlatResource\Pages;
use App\Filament\Resources\FlatResource\Pages\ViewFlat;
use App\Filament\Resources\Building\FlatResource\RelationManagers;
use App\Filament\Resources\Building\FlatResource\RelationManagers\DocumentsRelationManager;

class FlatResource extends Resource
{
    protected static ?string $model = Flat::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Units';
    protected static ?string $navigationGroup = 'Flat Management';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,])
                    ->schema([
                    TextInput::make('property_number')
                        ->label('Unit Number')
                        ->required()
                        ->alphaDash()
                        ->placeholder('Unit Number'),
                    Select::make('owner_association_id')
                        ->required()
                        ->options(function(){
                            return OwnerAssociation::where('role','Property Manager')->pluck('name','id');
                        })
                        ->visible(auth()->user()->role->name === 'Admin')
                        ->live()
                        ->preload()
                        ->searchable()
                        ->label('Select Property Manager'),
                    Select::make('building_id')
                        ->rules(['exists:buildings,id'])
                        ->relationship('building', 'name')
                        ->options(function(Get $get){
                            $buildings = DB::table('building_owner_association')->where('owner_association_id',$get('owner_association_id') ?? auth()->user()->owner_association_id)->pluck('building_id');
                            return Building::whereIn('id',$buildings)->pluck('name','id');
                        })
                        ->reactive()
                        ->preload()
                        ->required()
                        ->searchable()
                        ->placeholder('Building')
                        ->label('Select Building'),
                    Select::make('property_type')
                        ->options([
                            'Shop' => 'Shop',
                            'Office' => 'Office',
                            'Unit' => 'Unit',
                        ])
                        ->required()
                        ->searchable(),
                    TextInput::make('suit_area')
                        ->placeholder('NA')
                        ->numeric(),
                    TextInput::make('actual_area')
                        ->placeholder('NA')
                        ->numeric(),
                    TextInput::make('balcony_area')
                        ->placeholder('NA')
                        ->numeric(),
                    TextInput::make('applicable_area')
                        ->placeholder('NA')
                        ->numeric(),
                    TextInput::make('virtual_account_number')
                        ->placeholder('NA')
                        ->numeric(),
                    TextInput::make('parking_count')
                        ->placeholder('NA')
                        ->numeric(),
                    TextInput::make('plot_number')
                        ->placeholder('NA')
                        ->numeric(),
                    TextInput::make('makhani_number')
                        ->placeholder('NA')
                        ->hidden(auth()->user()->role->name === 'OA')
                        ->numeric(),
                    TextInput::make('dewa_number')
                        ->placeholder('NA')
                        ->hidden(auth()->user()->role->name === 'OA')
                        ->numeric(),
                    TextInput::make('etisalat/du_number')
                        ->placeholder('NA')
                        ->hidden(auth()->user()->role->name === 'OA')
                        ->numeric(),
                    TextInput::make('btu/ac_number')
                        ->placeholder('NA')
                        ->hidden(auth()->user()->role->name === 'OA')
                        ->numeric(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                TextColumn::make('property_number')
                    ->default('NA')
                    ->searchable()
                    ->label('Unit Number'),
                TextColumn::make('building.name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('suit_area')
                    ->formatStateUsing(fn ($record) => number_format($record->suit_area, 2))
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('actual_area')
                    ->formatStateUsing(fn ($record) => number_format($record->actual_area, 2))
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('balcony_area')
                    ->formatStateUsing(fn ($record) => number_format($record->balcony_area, 2))
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('applicable_area')
                    ->formatStateUsing(fn ($record) => number_format($record->applicable_area, 2))
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('virtual_account_number')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('parking_count')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('plot_number')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('tenants.role')
                    ->label('Occupied By')
                    ->default('NA')
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->options(function () {
                        if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
                            return Building::all()->pluck('name', 'id');
                        }
                        else{
                             return Building::where('owner_association_id', auth()->user()?->owner_association_id)
                            ->pluck('name', 'id');
                    }
                    })
                    ->searchable()
                    ->label('Building')
                    ->preload()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                //Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        if(auth()->user()?->role?->name === 'Property Manager'){
            return [
                // FlatResource\RelationManagers\FlatDomesticHelpRelationManager::class,
                // FlatResource\RelationManagers\FlatTenantRelationManager::class,
                // FlatResource\RelationManagers\FlatVisitorRelationManager::class,
                // FlatResource\RelationManagers\UserRelationManager::class,
                DocumentsRelationManager::class,
            ];
        }
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlats::route('/'),
            'create' => Pages\CreateFlat::route('/create'),
            'edit' => Pages\EditFlat::route('/{record}/edit'),
            // 'view' => ViewFlat::route('/{record}'),
        ];
    }
}
