<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use App\Models\OfferPromotion;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class OfferPromotionsRelationManager extends RelationManager
{
    protected static string $relationship = 'offerPromotions';
    protected static ?string $modelLabel  = 'Exclusive Offers';
    protected static ?string $title       = 'Exclusive Offers';

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
                                if (!preg_match('/^(?!\s)(?!.*\s$)(?!\d+$)[a-zA-Z0-9\s]+$/', $value)) {
                                    $fail('The Name format is invalid. It must contain only alphabetic characters and numbers.');
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
                    // MarkdownEditor::make('description')
                    //     ->toolbarButtons([
                    //         'bold',
                    //         'bulletList',
                    //         'italic',
                    //         'link',
                    //         'orderedList',
                    //         'redo',
                    //         'undo',
                    //     ])
                    //     ->required()
                    //     ->maxLength(400)
                    //     ->columnSpan([
                    //         'sm' => 1,
                    //         'md' => 1,
                    //         'lg' => 2,
                    //     ]),
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
                    Toggle::make('active')
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
                ToggleColumn::make('active')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->visible(function(RelationManager $livewire){
                    return OfferPromotion::where('building_id',$livewire->ownerRecord->id)->count() < 10;
                }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
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
