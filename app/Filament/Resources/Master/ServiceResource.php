<?php

namespace App\Filament\Resources\Master;

use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Service;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\Master\ServiceResource\Pages\EditService;
use App\Filament\Resources\Master\ServiceResource\Pages\ListServices;
use App\Filament\Resources\Master\ServiceResource\Pages\CreateService;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Inhouse Service';
    protected static ?string $navigationGroup = 'Master';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 1,
                ])
                    ->schema([
                        TextInput::make('name')
                            ->rules(['max:50', 'string'])
                            ->required()
                            ->disabled()
                            ->placeholder('Name'),
                        Hidden::make('type')
                            ->default('inhouse'),
                        FileUpload::make('icon')
                            ->acceptedFileTypes(['image/jpeg', 'image/png'])
                            ->disk('s3')
                            ->directory('dev')
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
        $query = Service::where('custom', [0, NULL]);

        return $table
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
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    //DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                //CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //ServiceResource\RelationManagers\VendorsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServices::route('/'),
            //'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
}
