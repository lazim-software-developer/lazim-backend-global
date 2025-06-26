<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Asset;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\Vendor\Vendor;
use Filament\Facades\Filament;
use App\Models\TechnicianAssets;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\TechnicianAssetsResource\Pages;
use App\Filament\Resources\TechnicianAssetsResource\RelationManagers;
use App\Filament\Resources\TechnicianAssetsResource\RelationManagers\AssetMaintenancesRelationManager;

class TechnicianAssetsResource extends Resource
{
    protected static ?string $model = TechnicianAssets::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('building_id')
                    ->relationship('building', 'name')
                    ->preload()
                    ->options(function () {
                        $oaId = auth()->user()?->owner_association_id;
                        return Building::where('owner_association_id', $oaId)
                            ->pluck('name', 'id');
                    })
                    ->live()
                    ->searchable()
                    ->label('Building'),
                Select::make('asset_id')
                    ->relationship('asset', 'name')
                    ->options(function () {
                        $BuildingId = Building::all()->where('owner_association_id', auth()->user()?->owner_association_id)->pluck('id')->toArray();
                        return Asset::whereIn('building_id', $BuildingId)->pluck('name', 'id');
                    })
                    ->preload()
                    ->searchable()
                    ->label('Asset name'),
                Select::make('technician_id')
                    ->relationship('user', 'first_name')
                    ->options(function () {
                        return User::where('role_id', 13)
                            ->select('id', 'first_name')
                            ->pluck('first_name', 'id')
                            ->toArray();
                    })
                    ->preload()
                    ->searchable()
                    ->label('Technician name'),
                Select::make('vendor_id')
                    ->relationship('vendor', 'name')
                    ->options(function (Get $get) {
                        $ServiceList = DB::table('building_service')->where('building_id', $get('building_id'))->pluck('service_id')->toArray();
                        $VendorList = DB::table('service_vendor')->whereIn('service_id', $ServiceList)->pluck('vendor_id')->toArray();
                        return Vendor::whereIn('id', $VendorList)
                            ->select('id', 'name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->preload()
                    ->searchable()
                    ->label('Vendor name'),

                Toggle::make('active')
                    ->rules(['boolean']),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('asset.name')->searchable()->label('Asset name')->sortable(),
                TextColumn::make('user.first_name')->searchable()->label('Technician name')->sortable(),
                TextColumn::make('vendor.name')->searchable()->label('Vendor name')->sortable(),
                TextColumn::make('building.name')->searchable()->label('Building name')->sortable(),
                IconColumn::make('active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->relationship('building', 'name', function (Builder $query) {
                        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                            $query->where('owner_association_id', Filament::getTenant()?->id);
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->label('Building'),
                SelectFilter::make('vendor_id')
                    ->relationship('vendor', 'name', function (Builder $query) {
                        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                            $query->where('owner_association_id', auth()->user()?->owner_association_id);
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->label('Vendor'),

                SelectFilter::make('technician_id')
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            $userId = TechnicianAssets::pluck('technician_id');
                            return User::whereIn('id', $userId)->pluck('first_name', 'id');
                        } else {
                            $userId = TechnicianAssets::where('owner_association_id', auth()->user()->owner_association_id)->pluck('technician_id');
                            return User::whereIn('id', $userId)->pluck('first_name', 'id');
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->label('Technician')
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            AssetMaintenancesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTechnicianAssets::route('/'),
            // 'create' => Pages\CreateTechnicianAssets::route('/create'),
            'view' => Pages\ViewTechnicianAssets::route('/{record}'),
            // 'edit' => Pages\EditTechnicianAssets::route('/{record}/edit'),
        ];
    }
}
