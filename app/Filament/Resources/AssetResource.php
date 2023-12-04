<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use Filament\Tables;
use App\Models\Asset;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Forms\Components\QrCode;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AssetResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AssetResource\RelationManagers;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Facility Bookings';
    protected static ?string $navigationGroup = 'Vendor Management';

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
                        Select::make('building_id')
                            ->relationship('building', 'name')
                            ->options(function () {
                                $oaId = auth()->user()->owner_association_id;
                                return Building::where('owner_association_id', $oaId)
                                    ->pluck('name', 'id');
                            })
                            ->preload()
                            ->searchable()
                            ->live()
                            ->label('Building Name'),
                        TextInput::make('name')
                            ->rules([
                                fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                    if (Asset::where('building_id', $get('building_id'))->where('name', $value)->exists()) {
                                        $fail('The Name is already taken for this Building.');
                                    }
                                },
                            ])
                            ->required()
                            ->label('Asset Name'),
                        TextInput::make('location')
                            ->label('Location'),
                        TextInput::make('description')
                            ->label('Description'),
                        Select::make('service_id')
                            ->relationship('service', 'name')
                            ->preload()
                            ->searchable()
                            ->label('Service'),
                    ]),
                QrCode::make('qr_code')
                    ->label('QR Code')
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 2,
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->label('Asset Name'),
                TextColumn::make('description')->searchable()->label('Description'),
                TextColumn::make('location')->label('Location'),
                TextColumn::make('service.name')->searchable()->label('Service'),
                TextColumn::make('building.name')->searchable()->label('Building Name'),
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
            'index' => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'edit' => Pages\EditAsset::route('/{record}/edit'),
        ];
    }
}
