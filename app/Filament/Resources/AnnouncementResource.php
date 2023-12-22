<?php

namespace App\Filament\Resources;

use DateTime;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AnnouncementResource\Pages;
use App\Filament\Resources\AnnouncementResource\RelationManagers;
use App\Models\Master\Role;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $modelLabel = 'Announcement';
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
                                $set('scheduled_at',null);
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
                                if(Role::where('id',auth()->user()->role_id)->first()->name == 'Admin'){
                                    return Building::all()->pluck('name','id');
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
                    ])
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
                    ->relationship('building', 'name',function (Builder $query){
                        if(Role::where('id',auth()->user()->role_id)->first()->name == 'Admin')
                        {
                            $query->all();
                        }
                        $query->where('owner_association_id',auth()->user()->owner_association_id);
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
