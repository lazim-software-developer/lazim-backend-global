<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use Illuminate\Support\Str;
use App\Models\Building\Flat;
use App\Models\Vendor\Vendor;
use App\Models\Master\Service;
use Filament\Facades\Filament;
use App\Models\TechnicianVendor;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use App\Models\Building\Complaint;
use Illuminate\Support\Facades\DB;
use App\Models\Complaintscomplaint;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use App\Models\Vendor\ServiceVendor;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\ComplaintscomplaintResource\Pages;
use App\Filament\Resources\ComplaintscomplaintResource\RelationManagers;
use App\Filament\Resources\ComplaintscomplaintResource\RelationManagers\CommentsRelationManager;

class ComplaintscomplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Happiness Center Complaints';

    protected static ?string $navigationGroup = 'Happiness center';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Complaint Details')->schema([
                    Grid::make([
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 2,
                    ])->schema([
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
                        Select::make('vendor_id')
                            ->relationship('vendor', 'name')
                            ->preload()
                            ->required()
                            ->options(function (Complaint $record, Get $get) {
                                $serviceVendor = ServiceVendor::where('service_id', $get('service_id'))->pluck('vendor_id');
                                if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                                    return Vendor::whereIn('id', $serviceVendor)->whereHas('ownerAssociation', function ($query) {
                                        $query->where('owner_association_id', Filament::getTenant()->id);
                                    })->pluck('name', 'id');
                                }
                                return Vendor::whereIn('id', $serviceVendor)->pluck('name', 'id');
                            })
                            ->disabled(function (Complaint $record) {
                                if ($record->vendor_id == null) {
                                    return false;
                                }
                                return true;
                            })
                            ->live()
                            ->searchable()
                            ->label('Vendor'),
                        Select::make('flat_id')
                            ->rules(['exists:flats,id'])
                            ->required()
                            ->disabled()
                            ->relationship('flat', 'property_number')
                            ->searchable()
                            ->preload()
                            ->placeholder('Unit Number'),
                        TextInput::make('ticket_number')->disabled(),
                        Select::make('technician_id')
                            ->relationship('technician', 'first_name')
                            ->options(function (Complaint $record, Get $get) {
                                $technician_vendor = DB::table('service_technician_vendor')->where('service_id', $record->service_id)->pluck('technician_vendor_id');
                                $technicians = TechnicianVendor::find($technician_vendor)->where('vendor_id', $get('vendor_id'))->pluck('technician_id');
                                return User::find($technicians)->pluck('first_name', 'id');
                            })
                            ->disabled(function (Complaint $record) {
                                return $record->status == 'closed';
                            })
                            ->preload()
                            ->searchable()
                            ->label('Technician'),
                        TextInput::make('priority')
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, Closure $fail) {
                                        if ($value < 1 || $value > 3) {
                                            $fail('The priority field accepts 1, 2 and 3 only.');
                                        }
                                    };
                                },
                            ])
                            ->disabled(function (Complaint $record) {
                                return $record->status == 'closed';
                            })
                            ->numeric(),
                        DatePicker::make('due_date')
                            ->disabled(function (Complaint $record) {
                                return $record->status == 'closed';
                            })
                            ->rules(['date'])
                            ->placeholder('Due Date'),
                        TextInput::make('category')
                            ->disabled(),
                        TextInput::make('open_time')->disabled(),
                        TextInput::make('close_time')->disabled()->default('NA'),
                        Textarea::make('complaint')
                            ->disabled()
                            ->placeholder('Complaint'),
                        TextInput::make('type')->label('Type')
                            ->disabled()
                            ->default('NA'),
                        Toggle::make('Urgent')
                            ->disabled()
                            ->formatStateUsing(function ($record) {
                                if ($record->priority == 1) {
                                    return true;
                                } else {
                                    return false;
                                }
                            })
                            ->default(false)
                            ->hidden(function ($record) {
                                if ($record->type == 'personal') {
                                    return false;
                                } else {
                                    return true;
                                }
                            })
                            ->disabled(),
                    ])
                ]),
                Repeater::make('media')
                    ->relationship()
                    ->disabled()
                    ->schema([
                        FileUpload::make('url')
                            ->disk('s3')
                            ->directory('dev')
                            ->maxSize(2048)
                            ->openable(true)
                            ->downloadable(true)
                            ->label('File'),
                    ])
                    ->columns(2)
                    ->visible(function ($record) {
                        return $record && $record->media()->exists();
                    }),
                Section::make('Status and Remarks')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'open' => 'Open',
                                'in-progress' => 'In-Progress',
                                'closed' => 'Closed',
                            ])
                            ->disabled(function (Complaint $record) {
                                return $record->status == 'closed';
                            })
                            ->searchable()
                            ->live(),
                        TextArea::make('remarks')
                            ->rules(['max:250'])
                            ->disabled(function (Complaint $record) {
                                return $record->status == 'closed';
                            })
                            ->required(),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_number')
                    ->toggleable()
                    ->sortable()
                    ->default('NA')
                    ->limit(20)
                    ->searchable()
                    ->label('Ticket number'),
                TextColumn::make('building.name')
                    ->default('NA')

                    ->sortable()
                    ->searchable()
                    ->limit(50),
                TextColumn::make('flat.property_number')->sortable(),
                TextColumn::make('type')
                    ->formatStateUsing(fn(string $state) => Str::ucfirst($state))
                    ->default('NA'),
                TextColumn::make('user.first_name')
                    ->toggleable()
                    ->sortable()
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
                    ->limit(50)
                    ->formatStateUsing(fn($state) => ucfirst($state)),

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
                                    $buildingId = DB::table('building_owner_association')->where('owner_association_id', auth()->user()?->owner_association_id)->where('active', true)->pluck('building_id');
                                    return Building::whereIn('id', $buildingId)->pluck('name', 'id');
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
                            $query->where('building_id', $data['Building']);
                        }
                        if (!empty($data['flat'])) {
                            $query->where('flat_id', $data['flat']);
                        }

                        return $query;
                    }),

                SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in-progress' => 'In-progress',
                        'closed' => 'Closed'
                    ])
                    ->columns(2)
            ], FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->bulkActions([
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])


            ->actions([]);
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
            'index' => Pages\ListComplaintscomplaints::route('/'),
            // 'view' => Pages\ViewComplaintscomplaints::route('/{record}'),
            'edit' => Pages\EditComplaintscomplaint::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_any_complaintscomplaint');
    }

    public static function canView(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_complaintscomplaint');
    }

    public static function canCreate(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('create_complaintscomplaint');
    }

    public static function canEdit(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('update_complaintscomplaint');
    }
}
