<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ComplaintResource\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\SnagsResource\Pages;
use App\Models\Building\Building;
use App\Models\Building\Complaint;
use App\Models\Master\Role;
use App\Models\TechnicianVendor;
use App\Models\User\User;
use App\Models\Vendor\ServiceVendor;
use App\Models\Vendor\Vendor;
use Carbon\Carbon;
use Closure;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class SnagsResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon  = 'heroicon-s-swatch';
    protected static ?string $modelLabel      = 'Snags';
    protected static ?string $navigationGroup = 'Security';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->columns(2)
                    ->schema([
                        Section::make('Snag Details')
                            ->schema([
                                Grid::make([
                                    'sm' => 1,
                                    'md' => 1,
                                    'lg' => 2,
                                ])
                                    ->schema([
                                        Select::make('building_id')
                                            ->label('Building')
                                            ->required()
                                            ->afterStateUpdated(function (callable $set) {
                                                $set('user_id', null);
                                            })
                                            ->rules(['exists:buildings,id'])
                                            ->options(function () {
                                                $role = auth()->user()->role->name;
                                                if ($role == 'Admin') {
                                                    return Building::all()->pluck('name', 'id');
                                                } elseif (in_array($role, ['Property Manager', 'OA'])) {
                                                    $buildings = DB::table('building_owner_association')
                                                        ->where('owner_association_id', auth()->user()?->owner_association_id)
                                                        ->where('active', true)->pluck('building_id');
                                                    return Building::whereIn('id', $buildings)->pluck('name', 'id');

                                                } else {
                                                    return Building::where('owner_association_id', auth()->user()?->owner_association_id)
                                                        ->pluck('name', 'id');
                                                }
                                            })
                                            ->reactive()
                                            ->disabledOn('edit')
                                            ->preload()
                                            ->searchable()
                                            ->placeholder('Building')
                                            ->live(),
                                        Select::make('service_id')
                                            ->relationship('service', 'name')
                                            ->preload()
                                            ->disabledOn('edit')
                                            ->searchable()
                                            ->label('Service'),
                                        Select::make('user_id')
                                            ->label('Gatekeeper')
                                        // ->relationship('user', 'first_name')
                                            ->options(function (Get $get) {

                                                if (is_null($get('building_id'))) {
                                                    return [];
                                                } else {
                                                    $userId = DB::table('building_pocs')
                                                        ->where('building_id', $get('building_id'))
                                                        ->where('active', true)->value('user_id');
                                                    return User::where('id', $userId)->pluck('first_name', 'id');
                                                }
                                            })
                                            ->disabledOn('edit')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        Select::make('vendor_id')
                                            ->relationship('vendor', 'name')
                                            ->preload()
                                        // ->required()
                                            ->disabled(function ($get) {
                                                return $get('status') == 'closed';
                                            })
                                            ->afterStateUpdated(function (callable $set) {
                                                $set('technician_id', null);
                                            })
                                            ->reactive()
                                            ->options(function (Get $get) {
                                                $serviceVendor = ServiceVendor::where('service_id', $get('service_id'))
                                                    ->pluck('vendor_id');
                                                if (Role::where('id', auth()->user()->role_id)->first()
                                                    ->name == 'Property Manager') {
                                                    return Vendor::whereHas('ownerAssociation', function ($query) {
                                                        $query->where('owner_association_id',
                                                            auth()->user()->owner_association_id);
                                                    })->whereIn('id', $serviceVendor)
                                                        ->pluck('name', 'id');

                                                }
                                                if (Role::where('id', auth()->user()->role_id)
                                                    ->first()->name != 'Admin') {
                                                    return Vendor::whereHas('ownerAssociation', function ($query) {
                                                        $query->where('owner_association_id',
                                                            Filament::getTenant()->id);
                                                    })->whereIn('id', $serviceVendor)->pluck('name', 'id');
                                                }
                                                return Vendor::whereIn('id', $serviceVendor)->pluck('name', 'id');
                                            })
                                        // ->disabled(function (Complaint $record) {
                                        //     if ($record->vendor_id == null) {
                                        //         return false;
                                        //     }
                                        //     return true;

                                        // })
                                            ->live()
                                            ->searchable()
                                            ->label('Vendor name'),

                                        Select::make('technician_id')
                                            ->relationship('technician', 'first_name')
                                            ->options(function (Get $get) {
                                                $technician_vendor = DB::table('service_technician_vendor')
                                                    ->where('service_id', $get('service_id'))
                                                    ->pluck('technician_vendor_id');
                                                $technicians = TechnicianVendor::find($technician_vendor)
                                                    ->where('vendor_id', $get('vendor_id'))
                                                    ->pluck('technician_id');

                                                return User::find($technicians)->pluck('first_name', 'id');
                                            })
                                            ->disabled(function ($get) {
                                                return $get('status') == 'closed';
                                            })
                                            ->preload()
                                            ->searchable()
                                            ->label('Technician name'),
                                        TextInput::make('priority')
                                            ->rules([function () {
                                                return function (string $attribute, $value, Closure $fail) {
                                                    if ($value < 1 || $value > 3) {
                                                        $fail('The priority field accepts 1, 2 and 3 only.');
                                                    }
                                                };
                                            },
                                            ])
                                            ->disabled(function (callable $get) {
                                                if ($get('status') == 'closed') {
                                                    return true;
                                                }
                                                return false;
                                            })
                                            ->numeric(),
                                        DatePicker::make('due_date')
                                            ->minDate(now()->format('Y-m-d'))
                                        // ->disabled(function (callable $get) {
                                        //     if ($get('status') == 'closed') {
                                        //         return true;
                                        //     }
                                        //     return false;
                                        // })
                                            ->rules(['date'])
                                            ->placeholder('Due Date'),
                                        Select::make('category')->required()
                                            ->disabledOn('edit')
                                            ->options(function () {
                                                return DB::table('services')->pluck('name', 'name')->toArray();
                                            })
                                            ->searchable()
                                            ->native(false),

                                        Textarea::make('complaint')
                                            ->disabledOn('edit')
                                            ->placeholder('Complaint'),

                                    ])
                            ]),
                        Repeater::make('media')
                            ->relationship()
                            ->columnSpanFull()
                        // ->disabledOn('edit')
                            ->schema([
                                FileUpload::make('url')
                                    ->disk('s3')
                                    ->directory('dev')
                                    ->maxSize(2048)
                                    ->openable(true)
                                    ->helperText('Accepted file types: jpg, jpeg, png / Max file size: 2MB')
                                    ->downloadable(true)
                                    ->label('File'),
                                // ->required(),
                            ])
                            ->deletable(false)
                            ->addable(false)
                            ->defaultItems(1),
                        // Select::make('service_id')
                        //     ->relationship('service', 'name')
                        //     ->preload()
                        //     ->disabled()
                        //     ->searchable()
                        //     ->label('Service'),
                        DateTimePicker::make('open_time')
                            ->visibleOn('edit')
                        // ->disabled(function (callable $get) {
                        //     if ($get('status') == 'closed') {
                        //         return true;
                        //     }
                        //     return false;
                        // })
                            ->disabled(),

                        DateTimePicker::make('close_time')
                            ->visibleOn('edit')
                            ->reactive()
                            ->disabled(), // Make it disabled to prevent manual changes

                        Section::make('Status and Remarks')
                            ->columns(2)
                            ->schema([
                                Select::make('status')
                                    ->required()
                                    ->options([
                                        'open'   => 'Open',
                                        'closed' => 'Closed',
                                    ])
                                    ->default('open')
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state === 'closed') {
                                            $set('close_time', Carbon::now()->format('Y-m-d H:i:s'));
                                        } else {
                                            $set('close_time', null);
                                        }
                                    })
                                    ->reactive()
                                    ->searchable()
                                    ->live(),
                                Textarea::make('remarks')
                                    ->rules(['max:250'])
                                    ->visible(function (callable $get) {
                                        if ($get('status') == 'closed') {
                                            return true;
                                        }
                                        return false;
                                    })
                                    ->required(),
                            ]),

                        Hidden::make('complaintable_type')
                            ->default('App\Models\User\User'),

                        Hidden::make('complaintable_id')
                            ->default(auth()->user()->id),

                        Hidden::make('complaint_type')
                            ->default('snag')

                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('building.name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('user.first_name')
                    ->toggleable()
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('complaint')
                    ->toggleable()
                    ->default('NA')
                    ->limit(20)
                    ->searchable()
                    ->label('Complaint'),
                // TextColumn::make('complaint_details')
                //     ->toggleable()
                //     ->default('NA')
                //     ->limit(20)
                //     ->searchable()
                //     ->label('Complaint Details'),

                TextColumn::make('status')
                    ->toggleable()
                    ->searchable()
                    ->default('--')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'open'                                       => 'Open',
                        'closed'                                     => 'Closed',
                        '--'                                         => '--',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'open'                            => 'primary',
                        'closed'                          => 'success',
                        '--'                              => 'muted',
                    }),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
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
                // Tables\Actions\CreateAction::make(),
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
            'index'  => Pages\ListSnags::route('/'),
            'create' => Pages\CreateSnags::route('/create'),
            'edit'   => Pages\EditSnags::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_any_snags');
    }

    public static function canView(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_snags');
    }

    public static function canCreate(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('create_snags');
    }

    public static function canEdit(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('update_snags');
    }
}
