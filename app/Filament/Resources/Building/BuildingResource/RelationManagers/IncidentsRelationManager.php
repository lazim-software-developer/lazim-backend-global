<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use App\Models\Building\Complaint;
use App\Models\TechnicianVendor;
use App\Models\User\User;
use App\Models\Vendor\ServiceVendor;
use App\Models\Vendor\Vendor;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class IncidentsRelationManager extends RelationManager
{
    protected static string $relationship = 'incident';

    protected static ?string $modelLabel = 'Incident';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Incidents';
    }

    public function form(Form $form): Form
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
                        Repeater::make('media')
                            ->relationship()
                            ->disabled()
                            ->helperText(function($state){
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
                            ->columnSpan([
                                'sm' => 1,
                                'md' => 1,
                                'lg' => 2,
                            ]),
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
                                ->helperText(function($state){
                                    return $state == [] ? 'No Comments' : '';
                                })
                                ->schema([
                                    Grid::make([
                                        'sm' => 1,
                                        'md' => 1,
                                        'lg' => 2,
                                    ])->schema([
                                        Textarea::make('body')->label('comment')->required()->maxLength(50)
                                        ->readOnly(function($state){
                                            if($state != null){
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

                    ]),
            ]);

    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('complaint_type', ['incident']))
            ->columns([
                TextColumn::make('building.name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('user.first_name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('complaint')
                    ->toggleable()
                    ->default('NA')
                    ->limit(20)
                    ->searchable()
                    ->label('Incident Details'),
                // TextColumn::make('complaint_details')
                //     ->toggleable()
                //     ->default('NA')
                //     ->searchable()
                //     ->label('Complaint Details'),
                TextColumn::make('status')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
            ]);
    }
}
