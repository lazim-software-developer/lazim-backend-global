<?php

namespace App\Filament\Resources\Building;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use App\Models\Building\FlatTenant;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\Building\FlatTenantResource\Pages;
use App\Filament\Resources\FlatTenantResource\RelationManagers\FamilyMembersRelationManager;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\DB;

class FlatTenantResource extends Resource
{
    protected static ?string $model = FlatTenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Residents';
    protected static ?string $navigationGroup = 'unit Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2
                ])
                    ->schema([
                        Select::make('building_id')
                            ->rules(['exists:buildings,id'])
                            ->relationship('building', 'name')
                            ->reactive()
                            ->disabled()
                            ->preload()
                            ->searchable()
                            ->placeholder('Building'),
                        Select::make('flat_id')
                            ->rules(['exists:flats,id'])
                            ->disabled()
                            ->required()
                            ->relationship('flat', 'property_number')
                            ->searchable()
                            ->preload()
                            ->label('Flat'),
                        Select::make('tenant_id')
                            ->rules(['exists:users,id'])
                            ->required()
                            ->disabled()
                            ->relationship('user', 'first_name')
                            ->searchable()
                            ->preload()
                            ->placeholder('User'),
                        DatePicker::make('start_date')->label('Created Date')
                            ->rules(['date'])
                            ->disabled()
                            ->required()
                            ->placeholder('Created Date'),
                        // DatePicker::make('end_date')
                        //     ->rules(['date'])
                        //     ->disabled()
                        //     ->placeholder('End Date'),
                        TextInput::make('role')
                            ->disabled()
                            ->placeholder('NA'),
                        // Toggle::make('primary')
                        //     ->disabled()
                        //     ->rules(['boolean']),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                TextColumn::make('building.name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('flat.property_number')
                    ->default('NA')
                    ->searchable()
                    ->label('Flat')
                    ->limit(50),
                TextColumn::make('user.first_name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('start_date')
                    ->label('Created Date')
                    ->date(),
                TextColumn::make('role')->default('NA'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('building')
                ->form([
                    Select::make('building_id')
                        ->label('Building')
                        ->native(false)
                        ->options(function () {
                            if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                return Building::all()->pluck('name', 'id');
                            } else {
                                $buildingId = DB::table('building_owner_association')
                                    ->where('owner_association_id', auth()->user()?->owner_association_id)
                                    ->where('active', true)
                                    ->pluck('building_id');
                                return Building::whereIn('id', $buildingId)->pluck('name', 'id');
                            }
                        })
                        ->searchable()
                        ->reactive()  // Make it reactive to trigger updates in flat selection
                        ->afterStateUpdated(function (callable $set, $state) {
                            $set('flat_id', null); // Reset the flat selection when the building changes
                        }),
                    
                    Select::make('flat_id')
                        ->label('Flat')
                        ->native(false)
                        ->options(function (callable $get) {
                            $selectedBuildingId = $get('building_id'); // Get selected building ID
                            if (empty($selectedBuildingId)) {
                                return [];  // If no building is selected, return an empty array
                            }
                            return Flat::where('building_id', $selectedBuildingId)->pluck('property_number', 'id');
                        })
                        ->searchable(),
                ])
                ->columns(2)
                ->query(function (Builder $query, array $data): Builder {
                    if (!empty($data['building_id'])) {
                        $query->where('building_id', $data['building_id']);
                    }
                    if (!empty($data['flat_id'])) {
                        $query->where('flat_id', $data['flat_id']);
                    }
                    return $query;
                })
            
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                //Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // FlatTenantResource\RelationManagers\DocumentsRelationManager::class,
            // FlatTenantResource\RelationManagers\ComplaintsRelationManager::class,
            FamilyMembersRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlatTenants::route('/'),
            //'create' => Pages\CreateFlatTenant::route('/create'),
            'edit' => Pages\EditFlatTenant::route('/{record}/edit'),
        ];
    }
}
