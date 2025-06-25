<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\Community\Poll;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use App\Models\Community\PollResponse;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\PollResource\Pages;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class PollResource extends Resource
{
    protected static ?string $model = Poll::class;
    protected static ?string $modelLabel      = 'Polls';


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make([
                'sm' => 1,
                'md' => 1,
                'lg' => 1,
            ])
                ->columns(2)
                ->schema([
                    TextInput::make('question')
                        ->rules([function () {
                            return function (string $attribute, $value, Closure $fail) {
                                if (strlen($value) > 180) {
                                    $fail('Question must be less than 180 characters.');
                                }
                            };
                        }])
                        ->required()
                        ->suffix('?')
                        ->label('Question'),
                    KeyValue::make('options')
                        ->columnSpanFull()
                        ->addActionLabel('Add Option')
                        ->default([
                            'option1' => '',
                            'option2' => '',
                            'option3' => '',
                            'option4' => '',
                            'option5' => '',
                        ])
                        ->helperText('Enter At least two options with values less than 50 characters.')
                        ->rules([
                            'required',
                            function () {
                                return function (string $attribute, $value, Closure $fail) {
                                    $countValidOptions = 0;
                                    $length = 0;
                                    foreach ($value as $option) {
                                        // Check if the option has a value and the value is less than 30 characters
                                        if (!empty($option)) {
                                            $countValidOptions++;
                                        }
                                        if (strlen($option) > 50) {
                                            $length++;
                                        }
                                    }
                                    // Check if at least two options have valid values
                                    if ($countValidOptions < 2) {
                                        $fail('At least two options are required with values less than 50 characters.');
                                    }
                                    if ($length > 0) {
                                        $fail('options values should be less than 50 characters.');
                                    }
                                };
                            },
                        ])
                        ->required()
                        ->addable(false)
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
                        ->required()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            $set('scheduled_at', null);
                            $set('ends_on', null);
                        }),
                    DateTimePicker::make('scheduled_at')
                        ->rules(['date'])
                        ->displayFormat('d-M-Y h:i A')
                        ->minDate(function ($record, $state) {
                            if ($record?->scheduled_at == null || $state != $record?->scheduled_at) {
                                return now();
                            }
                        })
                        ->required(function (callable $get) {
                            if ($get('status') == 'published') {
                                return true;
                            }
                            return false;
                        })
                        ->default(now())
                        ->placeholder('Scheduled At'),
                    DateTimePicker::make('ends_on')
                        ->rules(['date'])
                        ->displayFormat('d-M-Y h:i A')
                        ->minDate(function ($record, $state) {
                            if ($record?->ends_on == null || $state != $record?->ends_on) {
                                return now();
                            }
                        })
                        ->required(function (callable $get) {
                            if ($get('status') == 'published') {
                                return true;
                            }
                            return false;
                        })
                        ->default(now()->addDay())
                        ->placeholder('Scheduled At'),
                    Select::make('building')
                        ->relationship('building', 'name')
                        ->options(function () {
                            if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                return Building::all()->pluck('name', 'id');
                            }
                            return Building::where('owner_association_id', auth()->user()?->owner_association_id)->pluck('name', 'id');
                        })
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->required()
                        ->label('Building'),
                    Toggle::make('active')
                        ->rules(['boolean'])
                        ->default(true)
                        ->inline(false)
                        ->label('Active'),
                    Hidden::make('created_by')
                        ->default(auth()->user()->id),
                    ViewField::make('Responses')
                        ->visible(fn($record) => PollResponse::where('poll_id', $record?->id)->count() > 0)
                        ->view('forms.components.pollresponse'),

                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question')->limit(30)->searchable()->sortable(),
                TextColumn::make('options')->limit(30),
                TextColumn::make('status')->searchable(),
                TextColumn::make('scheduled_at')
                    ->default('NA')->sortable(),
                TextColumn::make('ends_on')
                    ->default('NA'),
                TextColumn::make('building.name')
                    ->searchable()
                    ->sortable()
                    ->default('NA')
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building')
                    ->relationship('building', 'name', function (Builder $query) {
                        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                            $query->where('buildings.owner_association_id', Filament::getTenant()?->id ?: auth()->user()?->owner_association_id);
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->label('Building'),
                SelectFilter::make('status')
                    ->options([
                        'published' => 'Published',
                        'draft' => 'Draft',
                    ])
            ], FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
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
