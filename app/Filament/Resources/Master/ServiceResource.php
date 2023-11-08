<?php

namespace App\Filament\Resources\Master;

use App\Filament\Resources\Master\ServiceResource\Pages\CreateService;
use App\Filament\Resources\Master\ServiceResource\Pages\EditService;
use App\Filament\Resources\Master\ServiceResource\Pages\ListServices;
use App\Models\Master\Service;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2])
                    ->schema([
                        TextInput::make('name')
                            ->rules(['max:50', 'string'])
                            ->required()
                            ->placeholder('Name'),
                        FileUpload::make('icon')
                            ->disk('s3')
                            ->directory('dev')
                            ->image()
                            ->required()
                            ->maxSize(2048),
                        Toggle::make('active')
                            ->label('Active')
                            ->default(1)
                            ->rules(['boolean']),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $query = Service::where('custom',[0,NULL]);

        return $table
            ->query($query)
            ->poll('60s')
            ->columns([
                TextColumn::make('name')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                IconColumn::make('active')
                    ->toggleable()
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ServiceResource\RelationManagers\VendorsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit'   => EditService::route('/{record}/edit'),
        ];
    }
}
