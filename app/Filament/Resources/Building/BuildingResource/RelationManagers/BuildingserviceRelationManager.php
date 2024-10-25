<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use App\Models\Master\Service;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BuildingserviceRelationManager extends RelationManager
{
    protected static string $relationship = 'buildingservice';
    protected static ?string $modelLabel  = 'Personal Service';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Personal Service';
    }

    public function form(Form $form): Form
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
                            ->placeholder('Name'),
                        Hidden::make('type')
                            ->default('inhouse'),
                        TextInput::make('code')
                            ->alphaDash()
                            ->required()
                            ->placeholder('NA'),
                        TextInput::make('payment_link')
                            ->placeholder('NA')
                            ->url(),
                        TextInput::make('price')
                            ->prefix('AED')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10000)
                            ->placeholder('NA'),
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

    public function table(Table $table): Table
    {
        return $table
            ->query(Service::query()->where('type', 'inhouse'))
            ->columns([
                TextColumn::make('name')->default('NA'),
                TextColumn::make('price')->default('NA'),
                IconColumn::make('active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),
                // TextColumn::make('payment_link')->default('NA'),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
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
}
