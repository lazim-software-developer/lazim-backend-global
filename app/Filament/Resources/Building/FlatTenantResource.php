<?php

namespace App\Filament\Resources\Building;

use App\Filament\Resources\Building\FlatTenantResource\Pages;
use App\Filament\Resources\FlatTenantResource\RelationManagers\FamilyMembersRelationManager;
use App\Filament\Resources\FlatTenantResource\RelationManagers\RentalDetailsRelationManager;
use App\Jobs\SendInactiveStatusToResident;
use App\Models\Building\Building;
use App\Models\Building\FlatTenant;
use App\Models\Master\Role;
use DB;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class FlatTenantResource extends Resource
{
    protected static ?string $model = FlatTenant::class;

    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel      = 'Residents';
    protected static ?string $navigationGroup = 'unit Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        Select::make('flat_id')
                            ->rules(['exists:flats,id'])
                            ->disabled()
                            ->required()
                            ->relationship('flat', 'property_number')
                            ->searchable()
                            ->preload()
                            ->label('Unit Number'),
                        Select::make('tenant_id')
                            ->rules(['exists:users,id'])
                            ->required()
                            ->disabled()
                            ->relationship('user', 'first_name')
                            ->searchable()
                            ->preload()
                            ->placeholder('User'),
                        Select::make('building_id')
                            ->rules(['exists:buildings,id'])
                            ->relationship('building', 'name')
                            ->reactive()
                            ->disabled()
                            ->preload()
                            ->searchable()
                            ->placeholder('Building'),
                        DatePicker::make('start_date')->label('Created Date')
                            ->rules(['date'])
                            ->disabled()
                            ->required()
                            ->placeholder('Created Date'),

                        DatePicker::make('start_date')
                            ->label('Contract Start Date')
                            ->disabledOn('edit')
                            ->visible(function ($record) {
                                if ($record->role == 'Tenant') {
                                    return true;
                                }return false;
                            }),

                        DatePicker::make('end_date')
                            ->label('Contract End Date')
                            ->disabledOn('edit')
                            ->visible(function ($record) {
                                if ($record->role == 'Tenant') {
                                    return true;
                                }return false;
                            }),
                        TextInput::make('role')
                            ->disabled()
                            ->placeholder('NA'),

                        TextInput::make('makani_number_url')
                            ->label('Makani Number')
                            ->disabledOn('edit')
                            ->visible(function ($record) {
                                if ($record->role == 'Owner') {
                                    return true;
                                }return false;
                            })
                            ->default(fn($record) => $record->makaniNumber?->url ?? 'NA'),

                        Toggle::make('active')
                            ->label('Active Status')
                            ->rules(['boolean'])
                            ->inline(false)
                            ->onIcon('heroicon-o-check-circle')
                            ->offIcon('heroicon-o-x-mark')
                            ->onColor('success')
                            ->offColor('danger')
                            ->visibleOn('edit')
                            ->afterStateUpdated(function (bool $state, $record) {
                                if ($state === false) {
                                    SendInactiveStatusToResident::dispatch($record);
                                }
                            }),
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
                TextColumn::make('flat.property_number')
                    ->default('NA')
                    ->searchable()
                    ->label('Unit Number')
                    ->limit(50),
                TextColumn::make('user.first_name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('start_date')
                    ->label('Created Date')
                    ->date(),
                TextColumn::make('building.name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('role')->default('NA'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return Building::all()->pluck('name', 'id');
                        } elseif (Role::where('id', auth()->user()->role_id)
                                ->first()->name == 'Property Manager') {
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
                    ->label('Building')
                    ->preload(),
            ])
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
            FamilyMembersRelationManager::class,
            RentalDetailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlatTenants::route('/'),
            //'create' => Pages\CreateFlatTenant::route('/create'),
            'edit'  => Pages\EditFlatTenant::route('/{record}/edit'),
        ];
    }
}
