<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\TechnicianAssets;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TechnicianAssetsResource\Pages;
use App\Filament\Resources\TechnicianAssetsResource\RelationManagers;
use App\Models\Vendor\Vendor;

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
                    ->live()
                    ->searchable()
                    ->label('Building Name'),
                Select::make('asset_id')
                    ->relationship('asset', 'name')
                    ->preload()
                    ->searchable()
                    ->label('Asset Name'),
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
                    ->label('Technician Name'),
                Select::make('vendor_id')
                    ->relationship('vendor', 'name')
                    ->options(function (Get $get) {
                        $ServiceList = DB::table('building_service')->where('building_id', $get('building_id'))->pluck('service_id')->toArray();
                        $VendorList = DB::table('service_vendor')->whereIn('service_id',$ServiceList)->pluck('vendor_id')->toArray();
                        return Vendor::whereIn('id',$VendorList)
                            ->select('id', 'name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->preload()
                    ->searchable()
                    ->label('Vendor Name'),

                Toggle::make('active')
                    ->rules(['boolean']),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('asset.name')->searchable()->label('Asset Name'),
                TextColumn::make('user.first_name')->searchable()->label('Technician Name'),
                TextColumn::make('vendor.name')->searchable()->label('Vendor Name'),
                TextColumn::make('building.name')->searchable()->label('Building Name'),
                IconColumn::make('active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListTechnicianAssets::route('/'),
            'create' => Pages\CreateTechnicianAssets::route('/create'),
            'edit' => Pages\EditTechnicianAssets::route('/{record}/edit'),
        ];
    }
}
