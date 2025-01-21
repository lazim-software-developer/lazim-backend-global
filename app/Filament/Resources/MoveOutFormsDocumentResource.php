<?php

namespace App\Filament\Resources;

use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use Filament\Facades\Filament;
use App\Models\Forms\MoveInOut;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\MoveOutFormsDocumentResource\Pages;
use App\Models\Building\Flat;
use Illuminate\Support\Facades\DB;

class MoveOutFormsDocumentResource extends Resource
{
    protected static ?string $model = MoveInOut::class;
    protected static ?string $modelLabel = 'Move Out';
    protected static ?string $pluralModelLabel = 'Move Out';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Forms Document';
    public static function form(Form $form): Form
    {
        return $form
    ->schema([

        // Personal Information Section
        Section::make('Personal Information')
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([
                    TextInput::make('name')->disabled(),
                    TextInput::make('email')->disabled(),
                    TextInput::make('phone')->disabled(),
                    TextInput::make('moving_date')->disabled(),
                    TextInput::make('moving_time')->disabled(),
                    Select::make('building_id')
                        ->relationship('building', 'name')
                        ->preload()
                        ->disabled()
                        ->searchable()
                        ->label('Building'),
                    Select::make('flat_id')
                        ->relationship('flat', 'property_number')
                        ->preload()
                        ->disabled()
                        ->searchable()
                        ->label('Flat'),
                ]),
            ]),

        // Document Uploads Section
        Section::make('Documents')
            ->columns(3)
            ->schema([
                FileUpload::make('noc_landlord')
                    ->visible(function (callable $get) {
                        return $get('noc_landlord') != null;
                    })
                    ->disk('s3')
                    ->directory('dev')
                    ->downloadable(true)
                    ->openable(true)
                    ->disabled()
                    ->previewable(true)
                    ->label('NOC Landlord'),

                FileUpload::make('cooling_final')
                    ->visible(function (callable $get) {
                        return $get('cooling_final') != null;
                    })
                    ->disk('s3')
                    ->directory('dev')
                    ->downloadable(true)
                    ->disabled()
                    ->previewable(true)
                    ->openable(true)
                    ->label('Cooling Final Bill'),

                FileUpload::make('contract')
                    ->visible(function (callable $get) {
                        return $get('contract') != null;
                    })
                    ->disk('s3')
                    ->directory('dev')
                    ->disabled()
                    ->downloadable(true)
                    ->openable(true)
                    ->label('Tenancy / Ejari'),

                FileUpload::make('gas_final')
                    ->visible(function (callable $get) {
                        return $get('gas_final') != null;
                    })
                    ->disk('s3')
                    ->directory('dev')
                    ->disabled()
                    ->downloadable(true)
                    ->openable(true)
                    ->label('Gas Final Bill'),

                FileUpload::make('gas_clearance')
                    ->disk('s3')
                    ->directory('dev')
                    ->disabled()
                    ->downloadable(true)
                    ->openable(true)
                    ->label('Gas Clearance'),

                FileUpload::make('cooling_clearance')
                    ->disk('s3')
                    ->directory('dev')
                    ->disabled()
                    ->downloadable(true)
                    ->openable(true)
                    ->label('Cooling Clearance'),

                FileUpload::make('dewa_final')
                    ->disk('s3')
                    ->directory('dev')
                    ->disabled()
                    ->downloadable(true)
                    ->openable(true)
                    ->label('Dewa Final Bill'),

                FileUpload::make('etisalat_final')
                    ->visible(function (callable $get) {
                        return $get('etisalat_final') != null;
                    })
                    ->disk('s3')
                    ->directory('dev')
                    ->disabled()
                    ->downloadable(true)
                    ->openable(true)
                    ->label('Etisalat Final Bill'),

                FileUpload::make('movers_license')
                    ->visible(function (callable $get) {
                        return $get('movers_license') != null;
                    })
                    ->disk('s3')
                    ->directory('dev')
                    ->disabled()
                    ->downloadable(true)
                    ->openable(true)
                    ->label("Movers ID's and Company License"),

                FileUpload::make('movers_liability')
                    ->visible(function (callable $get) {
                        return $get('movers_liability') != null;
                    })
                    ->disk('s3')
                    ->directory('dev')
                    ->disabled()
                    ->downloadable(true)
                    ->openable(true)
                    ->label('Movers Third Party Liability/Security Deposit'),
            ]),

        // Status and Remarks Section
        Section::make('Status and Remarks')
            ->columns(2)
            ->schema([
                Select::make('status')
                    ->options([
                        'approved' => 'Approve',
                        'rejected' => 'Reject',
                    ])
                    ->disabled(function (MoveInOut $record) {
                        return $record->status != null;
                    })
                    ->required()
                    ->searchable()
                    ->live(),

                TextInput::make('remarks')
                    ->rules(['max:150'])
                    ->visible(function (callable $get) {
                        return $get('status') == 'rejected';
                    })
                    ->disabled(function (MoveInOut $record) {
                        return $record->status != null;
                    })
                    ->required(),

                // Rejected Fields Section
                CheckboxList::make('rejected_fields')
                    ->label('Please select rejected fields')
                    ->options([
                        'noc_landlord' => 'NOC landlord',
                        'cooling_final' => 'Cooling final bill',
                        'contract' => 'Tenancy / Ejari',
                        'movers_license' => 'Movers license',
                        'movers_liability' => 'Movers liability',
                        'etisalat_final' => 'Etisalat final bill',
                        'dewa_final' => 'Dewa final bill',
                        'gas_clearance' => 'Gas clearance',
                        'cooling_clearance' => 'Cooling clearance',
                        'gas_final' => 'Gas final bill',
                    ])->columns(4)
                    ->visible(function (callable $get) {
                        return $get('status') == 'rejected';
                    }),
            ]),
    ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'move-out')->withoutGlobalScopes())
            ->columns([

                TextColumn::make('ticket_number')
                    ->searchable()
                    ->default('NA')
                    ->label('Ticket number'),
                TextColumn::make('name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->default('NA')
                    ->label('Flat')
                    ->limit(50),
                TextColumn::make('status')
                    ->searchable()
                    ->default('Pending')
                    ->limit(50),
                TextColumn::make('remarks')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('building')
                ->form([
                    Select::make('Building')
                        ->searchable()
                        ->options(function () {
                            if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                return Building::all()->pluck('name', 'id');
                            } else {
                                $buildingId = DB::table('building_owner_association')->where('owner_association_id',auth()->user()?->owner_association_id)->where('active',true)->pluck('building_id');
                                return Building::whereIn('id',$buildingId)->pluck('name', 'id');
                            }
                        })
                        ->reactive()
                        ->afterStateUpdated(function (callable $set) {
                            $set('flat', null);
                        }),
                    
                    Select::make('flat')
                        ->searchable()
                        ->options(function (callable $get) {
                            $buildingId = $get('Building'); // Get selected building ID
                            if (empty($buildingId)) {
                                return []; 
                            }
            
                            return Flat::where('building_id', $buildingId)->pluck('property_number', 'id');
                        }),
                ])
                ->columns(2) 
                ->query(function (Builder $query, array $data): Builder {
                    if (!empty($data['Building'])) {
                        $flatIds = Flat::where('building_id', $data['Building'])->pluck('id');
                        $query->whereIn('flat_id', $flatIds);
                    }
                    if (!empty($data['flat'])) {
                        $query->where('flat_id', $data['flat']);
                    }
            
                    return $query;
                }),

                Filter::make('status')
                    ->form([
                        Select::make('status')
                            ->options([
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'NA' => 'Pending'
                            ])
                            ->label('Status')
                            ->placeholder('Select Status')
                            ->required(),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        $selectedStatus = $data['status'] ?? null;
                        
                        if ($selectedStatus === 'NA') {
                            $query->whereNull('status')
                                    ->orWhereNotIn('status', ['approved', 'rejected']);
                        }elseif ($selectedStatus !== null) {
                            $query->where('status', $selectedStatus);
                        }

                        return $query;
                    })

            
            ])
            ->filtersFormColumns(3) 
            ->bulkActions([
                ExportBulkAction::make(),
                ])
            ->actions([
                //Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListMoveOutFormsDocuments::route('/'),
            // 'view' => Pages\ViewMoveOutFormsDocument::route('/{record}'),
            'edit' => Pages\EditMoveOutFormsDocument::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_any_move::out::forms::document');
    }

    public static function canView(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_move::out::forms::document');
    }

    public static function canCreate(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('create_move::out::forms::document');
    }

    public static function canEdit(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('update_move::out::forms::document');
    }
}
