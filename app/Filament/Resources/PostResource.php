<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers\CommentsRelationManager;
use App\Models\Building\Building;
use App\Models\Community\Post;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use function Laravel\Prompts\select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class PostResource extends Resource
{
    protected static ?string $model           = Post::class;
    protected static ?string $modelLabel      = 'Post';
    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Community';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make([
                'sm' => 1,
                'md' => 2,
                'lg' => 2,
            ])->schema([
                RichEditor::make('content')
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
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 2,
                    ]),

                Section::make()
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->searchable()
                            ->options([
                                'published' => 'Published',
                                'draft'     => 'Draft',
                            ])
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

                        Select::make('building')
                            ->relationship('building', 'name')
                            ->options(function () {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return Building::all()->pluck('name', 'id');
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Property Manager'
                                || OwnerAssociation::where('id', auth()->user()?->owner_association_id)
                                ->pluck('role')[0] == 'Property Manager') {
                                    $buildings = DB::table('building_owner_association')
                                        ->where('owner_association_id', auth()->user()?->owner_association_id)
                                        ->where('active', true)
                                        ->pluck('building_id');
                                    return Building::whereIn('id', $buildings)->pluck('name', 'id');
                                }
                                return Building::where('owner_association_id', auth()->user()?->owner_association_id)->pluck('name', 'id');
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Building')
                            ->columnSpan([
                                'sm' => 1,
                                'md' => 1,
                                'lg' => 1,
                            ]),
                    ]),

                Hidden::make('user_id')
                    ->default(auth()->user()->id),

                Hidden::make('owner_association_id')
                    ->default(auth()->user()?->owner_association_id),

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
                            ->rules('file|mimes:jpeg,jpg,png|max:2048')
                            ->directory('dev')
                            ->openable(true)
                            ->downloadable(true)
                            ->image()
                            ->maxSize(2048)
                            ->helperText('Accepted file types: jpg, jpeg, png / Max file size: 2MB')
                            ->required()
                            ->label('File'),

                    ])
                    ->columns(2)
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 2,
                    ]),
                Toggle::make('allow_like')
                    ->rules(['boolean'])
                    ->inline(false)
                    ->default(0),
                Toggle::make('allow_comment')
                    ->rules(['boolean'])
                    ->inline(false)
                    ->default(0),
                Toggle::make('active')
                    ->rules(['boolean'])
                    ->default(true)
                    ->inline(false)
                    ->label('Active'),
                TextInput::make('likes_count')
                    ->disabled()
                    ->hiddenOn('create'),
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
                    ->relationship('user', 'first_name', function (Builder $query) {
                        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                            $query->where('owner_association_id', auth()->user()?->owner_association_id)
                                ->where('active', true)
                                ->whereIn('role_id', Role::whereIn('name', ['OA', 'Owner', 'Tenant'])
                                    ->pluck('id'))
                                ->where(function ($query) {
                                    $query->whereHas('roles', function ($q) {
                                        $q->where('name', 'OA');
                                    })
                                    ->orWhereExists(function ($subquery) {
                                        $subquery->from('flat_tenants')
                                            ->whereColumn('flat_tenants.tenant_id', 'users.id')
                                            ->where('flat_tenants.owner_association_id', auth()->user()->owner_association_id)
                                            ->where('flat_tenants.active', true);
                                    });
                                });
                        }
                        $query->whereIn('role_id', Role::whereIn('name', ['OA', 'Owner', 'Tenant'])->pluck('id'));
                    })
                    ->searchable()
                    ->preload()
                    ->label('User'),
                SelectFilter::make('building_id')
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return Building::pluck('name', 'id');
                        } elseif (auth()->user()->role->name == 'Property Manager'
                        || OwnerAssociation::where('id', auth()->user()?->owner_association_id)
                                ->pluck('role')[0] == 'Property Manager') {
                            $buildingIds = DB::table('building_owner_association')
                                ->where('owner_association_id', auth()->user()->owner_association_id)
                                ->where('active', true)
                                ->pluck('building_id');

                            return Building::whereIn('id', $buildingIds)
                                ->pluck('name', 'id');

                        }
                        $oaId = auth()->user()?->owner_association_id;
                        return Building::where('owner_association_id', $oaId)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->label('Building'),
            ])
            ->actions([
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
            CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit'   => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_any_post');
    }

    public static function canView(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_post');
    }

    public static function canCreate(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('create_post');
    }

    public static function canEdit(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('update_post');
    }
}
