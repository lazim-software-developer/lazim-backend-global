<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OfferPromotionsRelationManager extends RelationManager
{
    protected static string $relationship = 'offerPromotions';
    protected static ?string $modelLabel  = 'Offer & Promotions';
    protected static ?string $title       = 'Offer & Promotions';

    public function form(Form $form): Form
    {
        return $form->schema([
            Grid::make([
                'sm' => 1,
                'md' => 1,
                'lg' => 2,
            ])
                ->schema([
                    TextInput::make('name')
                        ->rules([function () {
                            return function (string $attribute, $value, Closure $fail) {
                                if (!preg_match('/^[a-zA-Z]+(?:\s+[a-zA-Z]+)*$/', $value)) {
                                    $fail('The Name format is invalid. It must contain only alphabetic characters and spaces.');
                                }
                            };
                        }])
                        ->required()
                        ->maxLength(50),
                    TextInput::make('link')
                        ->maxLength(191),
                    FileUpload::make('image')
                        ->disk('s3')
                        ->rules('file|mimes:jpeg,jpg,png|max:2048')
                        ->directory('dev')
                        ->openable(true)
                        ->downloadable(true)
                        ->image()
                        ->maxSize(2048)
                        ->required()
                        ->label('Image')
                        ->columnSpan([
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 2,
                        ]),
                    MarkdownEditor::make('description')
                        ->toolbarButtons([
                            'bold',
                            'bulletList',
                            'italic',
                            'link',
                            'orderedList',
                            'redo',
                            'undo',
                        ])
                        ->required()
                        ->maxLength(400)
                        ->columnSpan([
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 2,
                        ]),
                    Datepicker::make('start_date')
                        ->required()
                        ->rules(['date'])
                        ->displayFormat('d-M-Y')
                        ->minDate(now()->format('d-M-Y'))
                        ->label('Start Date'),
                    DatePicker::make('end_date')
                        ->required()
                        ->displayFormat('d-M-Y')
                        ->rules(['date'])
                        ->minDate(now()->format('d-M-Y'))
                        ->label('End Date'),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\ImageColumn::make('image')->disk('s3'),
                Tables\Columns\TextColumn::make('start_date'),
                Tables\Columns\TextColumn::make('end_date'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
