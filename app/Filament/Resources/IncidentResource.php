<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use Filament\Tables;
use App\Models\Incident;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use App\Models\Building\Complaint;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\IncidentResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\IncidentResource\RelationManagers;
use App\Filament\Resources\ComplaintscomplaintResource\RelationManagers\CommentsRelationManager;

class IncidentResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Incidents';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        Select::make('building_id')
                            ->rules(['exists:buildings,id'])
                            ->relationship('building', 'name')
                            ->reactive()
                            ->disabled()
                            ->preload()
                            ->searchable()
                            ->placeholder('Building'),
                        Select::make('user_id')
                            ->relationship('user', 'first_name')
                            ->options(function () {
                                $tenants = DB::table('flat_tenants')->pluck('tenant_id');
                                // dd($tenants);
                                return DB::table('users')
                                    ->whereIn('users.id', $tenants)
                                    ->select('users.id', 'users.first_name')
                                    ->pluck('users.first_name', 'users.id')
                                    ->toArray();
                            })
                            ->disabled()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('User'),
                        DateTimePicker::make('open_time')->disabled()->label('created at'),
                        Textarea::make('complaint')->label('Incident Details')
                            ->disabled()
                            ->placeholder('Complaint'),
                        Select::make('status')
                            ->options([
                                'open'   => 'Open',
                                'closed' => 'Closed',
                            ])
                            ->disabled(function (Complaint $record) {
                                return $record->status != 'open';
                            })
                            ->searchable()
                            ->live(),
                        Repeater::make('comments')
                            ->relationship('comments')
                            ->helperText(function ($state) {
                                return $state == [] ? 'No Comments' : '';
                            })
                            ->schema([
                                Grid::make([
                                    'sm' => 1,
                                    'md' => 1,
                                    'lg' => 2,
                                ])->schema([
                                    Textarea::make('body')->label('comment')->required()->maxLength(50)
                                        ->readOnly(function ($state) {
                                            if ($state != null) {
                                                return true;
                                            }
                                            return false;
                                        }),
                                    Hidden::make('user_id')->default(auth()->user()?->id),
                                    DateTimePicker::make('created_at')->label('time')->format('MM/dd/yyyy hh:mm:ss tt')->default(now())->disabled()
                                ])
                            ])->deletable(false)
                            ->columnSpan([
                                'sm' => 1,
                                'md' => 1,
                                'lg' => 2,
                            ]),
                        Repeater::make('media')
                            ->relationship()
                            ->disabled()
                            ->helperText(function ($state) {
                                return $state == [] ? 'No media' : '';
                            })
                            ->schema([
                                FileUpload::make('url')
                                    ->disk('s3')
                                    ->directory('dev')
                                    ->maxSize(2048)
                                    ->openable(true)
                                    ->downloadable(true)
                                    ->label('File'),
                            ])
                            ->visible(function ($record) {
                                return $record && $record->media()->exists();
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('building.name')
                    ->default('NA')
                    ->sortable()
                    ->searchable()
                    ->limit(50),
                TextColumn::make('user.first_name')
                    ->default('NA')
                    ->sortable()
                    ->searchable()
                    ->limit(50),
                TextColumn::make('complaint')
                    ->label('Incident Deatils')
                    ->toggleable()
                    ->sortable()
                    ->default('NA')
                    ->limit(20)
                    ->searchable(),
                TextColumn::make('status')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
            ])
            ->filters([
                SelectFilter::make('building_id')
                    ->label('Building')
                    ->preload()
                    ->searchable()
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return Building::all()->pluck('name', 'id');
                        } else {
                            $buildingId = DB::table('building_owner_association')->where('owner_association_id', auth()->user()?->owner_association_id)->where('active', true)->pluck('building_id');
                            return Building::whereIn('id', $buildingId)->pluck('name', 'id');
                        }
                    }),

                SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'closed' => 'Closed'
                    ])
            ], FiltersLayout::AboveContent)
            ->actions([
                // Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIncidents::route('/'),
            'create' => Pages\CreateIncident::route('/create'),
            'view' => Pages\ViewIncident::route('/{record}'),
            'edit' => Pages\EditIncident::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_any_incident');
    }

    public static function canView(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_incident');
    }

    public static function canCreate(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('create_incident');
    }

    public static function canEdit(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('update_incident');
    }
}
