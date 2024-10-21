<?php

namespace App\Filament\Resources\Master;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Facility;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Master\FacilityResource\Pages;
use App\Filament\Resources\Master\FacilityResource\RelationManagers;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class FacilityResource extends Resource
{
    protected static ?string $model = Facility::class;
    protected static ?string $modelLabel = 'Amenities';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master';

    protected static bool $isScopedToTenant = false;
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
                        TextInput::make('name')
                            ->rules(['max:50', 'string'])
                            ->required()
                            ->placeholder('Name'),
                        // Select::make('building_id')
                        //     ->rules(['exists:buildings,id'])
                        //     ->relationship('buildings', 'name')
                        //     ->preload()
                        //     ->multiple()
                        //     ->searchable()
                        //     ->placeholder('Building'),

                        Toggle::make('active')
                            ->label('Active')
                            ->default(1)
                            ->rules(['boolean']),
                        FileUpload::make('icon')
                            ->acceptedFileTypes(['image/jpeg', 'image/png'])
                            ->disk('s3')
                            ->directory('dev')
                            ->required()
                            ->maxSize(2048),

                    ])
                    ->Columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $facilities = Facility::wherenotNuLL('name');

        return $table
            ->poll('60s')
            ->query($facilities)
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->limit(50),
                IconColumn::make('active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // FacilityResource\RelationManagers\FacilityBookingRelationManager::class,
            // FacilityResource\RelationManagers\BuildingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFacilities::route('/'),
            'create' => Pages\CreateFacility::route('/create'),
            'edit' => Pages\EditFacility::route('/{record}/edit'),
        ];
    }
}
