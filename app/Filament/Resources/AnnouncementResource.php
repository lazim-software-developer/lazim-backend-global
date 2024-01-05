<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Models\Building\Building;
use App\Models\Community\Post;
use App\Models\Master\Role;
use Closure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $modelLabel = 'Notice board';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Community';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make([
                'sm' => 1,
                'md' => 1,
                'lg' => 2,
            ])->schema([
                MarkdownEditor::make('content')
                    ->toolbarButtons([
                        'bold',
                        'bulletList',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'undo',
                    ])
                    ->rules([
                        'required', function () {
                            return function (string $attribute, $value, Closure $fail) {
                                $trimmedString = preg_replace('/\s+/', ' ', $value);
                                $trimmedString = trim($trimmedString);
                                if (strlen($value) > 400) {
                                    $fail('The content should be less than 400 characters.');
                                }
                                if (strlen($value) < 30) {
                                    $fail('The content should be greater than 30 characters.');
                                }
                            };
                        },
                    ])
                    ->required()
                    ->disabled(function ($record) {
                        return $record?->status == 'published';
                    })
                    ->live()
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 2,
                    ]),
                Select::make('status')
                    ->searchable()
                    ->options([
                        'published' => 'Published',
                        'draft' => 'Draft',
                    ])
                    ->disabled(function ($record) {
                        return $record?->status == 'published';
                    })
                    ->reactive()
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        $set('scheduled_at', null);
                    })
                    ->default('published')
                    ->required(),

                DateTimePicker::make('scheduled_at')
                    ->rules(['date'])
                    ->displayFormat('d-M-Y h:i A')
                // ->default(fn (Get $get) => $get('status') == null ? now() : dd($get('status')))
                    ->minDate(now())
                    ->live()
                    ->required(function (callable $get) {
                        if ($get('status') == 'published') {
                            return true;
                        }
                        return false;
                    })
                    ->disabled(function ($record) {
                        return $record?->status == 'published';
                    })
                    ->default(now())
                    ->placeholder('Scheduled At'),

                Select::make('building')
                    ->relationship('building', 'name')
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return Building::all()->pluck('name', 'id');
                        }
                        return Building::where('owner_association_id', auth()->user()->owner_association_id)->pluck('name', 'id');
                    })
                    ->searchable()
                    ->multiple()
                    ->disabled(function ($record) {
                        return $record?->status == 'published';
                    })
                    ->preload()
                    ->required(),

                Hidden::make('user_id')
                    ->default(auth()->user()->id),

                Hidden::make('owner_association_id')
                    ->default(auth()->user()->owner_association_id),

                Hidden::make('is_announcement')
                    ->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('scheduled_at')
                // ->dateTime()
                    ->default('NA'),
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('user.first_name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('user_id')
                    ->relationship('user', 'first_name')
                    ->searchable()
                    ->preload()
                    ->label('User'),
                SelectFilter::make('building_id')
                    ->relationship('building', 'name', function (Builder $query) {
                        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                            $query->where('owner_association_id', auth()->user()->owner_association_id);
                        }

                    })
                    ->searchable()
                    ->preload()
                    ->label('Building'),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
