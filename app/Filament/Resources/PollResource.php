<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Community\Poll;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\PollResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PollResource\RelationManagers;
use App\Filament\Resources\PollResource\RelationManagers\ResponsesRelationManager;

class PollResource extends Resource
{
    protected static ?string $model = Poll::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make([
                'sm' => 1,
                'md' => 1,
                'lg' => 1,
            ])->schema([
                        TextInput::make('question')
                            ->maxLength(200)
                            ->required()
                            ->suffix('?')
                            ->label('Question'),
                        KeyValue::make('options')
                            ->addActionLabel('Add Option')
                            ->default([
                                'option1' => '',
                                'option2' => '',
                                'option3' => '',
                                'option4' => '',
                                'option5' => '',
                            ])
                            ->required()
                            ->deletable(false)
                            ->editableKeys(false),
                        Select::make('status')
                            ->searchable()
                            ->options([
                                'published' => 'Published',
                                'draft' => 'Draft',
                            ])
                            ->reactive()
                            ->live()
                            ->default('published')
                            ->required(),
                        DateTimePicker::make('scheduled_at')
                            ->rules(['date'])
                            ->displayFormat('d-M-Y h:i A')
                            ->minDate(now())
                            ->required()
                            ->default(now())
                            ->placeholder('Scheduled At'),
                        DateTimePicker::make('ends_on')
                            ->rules(['date'])
                            ->displayFormat('d-M-Y h:i A')
                            ->minDate(now())
                            ->required()
                            ->default(now())
                            ->placeholder('Scheduled At'),
                        Select::make('building_id')
                            ->relationship('building', 'name')
                            ->options(function () {
                                return Building::where('owner_association_id', auth()->user()->owner_association_id)->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Building'),
                        Hidden::make('created_by')
                            ->default(auth()->user()->id),
                        ViewField::make('Responses')
                            ->view('forms.components.pollresponse'),

                    ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question'),
                TextColumn::make('options'),
                TextColumn::make('status'),
                TextColumn::make('scheduled_at')
                    ->dateTime(),
                TextColumn::make('ends_on')
                    ->dateTime(),
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            // ResponsesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPolls::route('/'),
            'create' => Pages\CreatePoll::route('/create'),
            'view' => Pages\ViewPoll::route('/{record}'),
            'edit' => Pages\EditPoll::route('/{record}/edit'),
        ];
    }
}
