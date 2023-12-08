<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use App\Models\Community\Post;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PostResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Building\Building;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;

use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\select;

class PostResource extends Resource {
    protected static ?string $model = Post::class;
    protected static ?string $modelLabel = 'Post';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Community';

    public static function form(Form $form): Form {
        return $form->schema([
            Grid::make([
                'sm' => 1,
                'md' => 1,
                'lg' => 2,
            ])->schema([
                        RichEditor::make('content')
                            ->disableToolbarButtons([
                                'codeBlock',
                                'h2',
                                'h3',
                                'attachFiles',
                                'blockquote',
                                'strike',
                            ])
                            ->minLength(10)
                            ->maxLength(255)
                            ->required()
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
                                'archived' => 'Archived',
                            ])
                            ->required()
                            ->default('draft'),

                        DateTimePicker::make('scheduled_at')
                            ->rules(['date'])
                            ->displayFormat('d-M-Y h:i A')
                            ->minDate(now())
                            ->required()
                            ->placeholder('Scheduled At'),

                        Select::make('building')
                            ->relationship('building', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Building')
                            ->columnSpan([
                                'sm' => 1,
                                'md' => 1,
                                'lg' => 2,
                            ]),

                        Hidden::make('user_id')
                            ->default(auth()->user()->id),

                        Hidden::make('owner_association_id')
                            ->default(auth()->user()->owner_association_id),

                        Hidden::make('is_announcement')
                            ->default(false),
                        Repeater::make('media')
                            ->relationship('media')
                            ->schema([
                                TextInput::make('name')
                                    ->rules(['max:30', 'regex:/^[a-zA-Z\s]*$/'])
                                    ->required()
                                    ->placeholder('Name'),
                                FileUpload::make('url')
                                    ->disk('s3')
                                    ->directory('dev')
                                    ->openable(true)
                                    ->downloadable(true)
                                    ->image()
                                    ->maxSize(2048)
                                    ->required()

                            ])
                            ->columnSpan([
                                'sm' => 1,
                                'md' => 1,
                                'lg' => 2,
                            ]),
                        Toggle::make('allow_like')->default(0),
                        Toggle::make('allow_comment')->default(0),
                    ])
        ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('scheduled_at')
                    ->dateTime(),
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
                    ->relationship('building', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Building'),
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

    public static function getRelations(): array {
        return [
            //
        ];
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
