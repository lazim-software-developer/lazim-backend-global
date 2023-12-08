<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Announcement;
use App\Models\Community\Post;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Set;
use Illuminate\Support\Str;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AnnouncementResource\Pages;
use App\Filament\Resources\AnnouncementResource\RelationManagers;
use Carbon\Carbon;
use DateTime;

class AnnouncementResource extends Resource {
    protected static ?string $model = Post::class;
    protected static ?string $modelLabel = 'Announcement';
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
                            ->reactive()
                            ->live()
                            // ->afterStateUpdated(function (Set $set, Get $get) {
                            //     $set('scheduled_at',Carbon::now()->addDay());
                            //     // dd($get('scheduled_at'));
                            // })
                            ->default('published')
                            ->required(),

                        DateTimePicker::make('scheduled_at')
                            ->rules(['date'])
                            ->displayFormat('d-M-Y h:i A')
                            // ->default(fn (Get $get) => $get('status') == null ? now() : dd($get('status')))
                            ->minDate(now())
                            ->live()
                            ->required()
                            ->default(now())
                            ->placeholder('Scheduled At'),

                        Select::make('building_id')
                            ->relationship('building', 'name')
                            ->options(function () {
                                return Building::where('owner_association_id', auth()->user()->owner_association_id)->pluck('name', 'id');
                            })
                            ->searchable()
                            ->multiple()
                            ->preload()
                            ->required(),

                        Hidden::make('user_id')
                            ->default(auth()->user()->id),

                        Hidden::make('owner_association_id')
                            ->default(auth()->user()->owner_association_id),

                        Hidden::make('is_announcement')
                            ->default(true),

                        // Repeater::make('media')
                        //     ->relationship('media')
                        //     ->schema([
                        //         TextInput::make('name')
                        //             ->rules(['max:30','regex:/^[a-zA-Z\s]*$/'])
                        //             ->required()
                        //             ->placeholder('Name'),
                        //         FileUpload::make('url')
                        //             ->disk('s3')
                        //             ->directory('dev')
                        //             ->maxSize(2048)
                        //             ->required()

                        //     ])
                        //     ->columnSpan([
                        //         'sm' => 1,
                        //         'md' => 1,
                        //         'lg' => 2,
                        //     ])

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
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
