<?php

namespace App\Filament\Resources\Vendor;

use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use Illuminate\Support\Str;
use App\Models\Vendor\Vendor;
use Filament\Facades\Filament;
use App\Jobs\AccountCreationJob;
use App\Jobs\VendorRejectionJob;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use App\Jobs\VendorAccountCreationJob;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Vendor\VendorResource\Pages;
use App\Filament\Resources\Vendor\VendorResource\RelationManagers\BuildingvendorRelationManager;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Vendor Management';
    protected static bool $shouldRegisterNavigation = true;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([

                    Hidden::make('owner_association_id')
                        ->default(auth()->user()?->owner_association_id),
                    Select::make('owner_id')
                        ->label('Vendor name')
                        ->rules(['exists:users,id'])
                        ->required()
                        ->disabled()
                        ->preload()
                        ->relationship('user', 'first_name')
                        ->searchable()
                        ->getSearchResultsUsing(fn(string $search): array => User::where('role_id', 2, "%{$search}%")->limit(50)->pluck('first_name', 'id')->toArray())
                        ->getOptionLabelUsing(fn($value): ?string => User::find($value)?->first_name)
                        ->placeholder('Vendor Name'),
                    TextInput::make('tl_number')->label('Trade license number')
                        ->rules(['max:50', 'string'])
                        ->disabled()
                        ->required()
                        ->unique(
                            'vendors',
                            'tl_number',
                            fn(?Model $record) => $record
                        )
                        ->placeholder('Trade License Number'),

                    DatePicker::make('tl_expiry')
                        ->label('Trade license expiry')
                        ->rules(['date'])
                        ->disabled()
                        ->required()
                        ->placeholder('Trade License Expiry'),
                    TextInput::make('address_line_1')
                        ->placeholder('NA')
                        ->disabled()
                        ->label('Address line 1'),
                    TextInput::make('address_line_2')
                        ->placeholder('NA')
                        ->disabled()
                        ->label('Address line 2'),
                    TextInput::make('landline_number')
                        ->placeholder('NA')
                        ->disabled()
                        ->label('Landline number'),
                    TextInput::make('website')
                        ->placeholder('NA')
                        ->disabled()
                        ->label('Website'),
                    TextInput::make('fax')
                        ->placeholder('NA')
                        ->disabled()
                        ->label('Fax'),
                    Select::make('status')
                        ->options([
                            'approved' => 'Approve',
                            'rejected' => 'Reject',
                        ])->hidden(function () {
                            return Role::where('id', auth()->user()?->role_id)->first()?->name == 'Admin';
                        })
                        ->visible(fn($record) => $record->ownerAssociation?->where('pivot.owner_association_id', Filament::getTenant()?->id)->first()?->pivot->status === null && $record->documents()->count() > 0 && $record->services()->count() > 0 && $record->managers()->count() > 0)
                        ->searchable()
                        ->live(),
                    TextInput::make('status')->disabled()->hidden(function () {
                        return Role::where('id', auth()->user()?->role_id)->first()?->name == 'Admin';
                    })
                        ->visible(fn($record) => $record->ownerAssociation?->where('pivot.owner_association_id', Filament::getTenant()?->id)->first()?->pivot->status != null),
                    Textarea::make('remarks')
                        ->maxLength(250)
                        ->rows(5)
                        ->required()
                        ->visible(function (callable $get) {
                            if ($get('status') == 'rejected') {
                                return true;
                            }
                            return false;
                        })->hidden(function () {
                            return Role::where('id', auth()->user()?->role_id)->first()?->name == 'Admin';
                        })
                        ->disabled(fn($record) => $record->ownerAssociation?->where('pivot.owner_association_id', Filament::getTenant()?->id)->first()?->pivot->status !== null),
                    Toggle::make('active')
                        ->rules(['boolean'])
                        ->required()
                        ->visible(fn($record) => $record->ownerAssociation?->where('pivot.owner_association_id', Filament::getTenant()?->id)->first()?->pivot->status === 'approved')
                        ->inline(false)
                        ->label('Active'),
                    // TextInput::make('remarks')
                    //     ->rules(['max:150'])
                    //     ->visible(fn($record) => $record->status === 'approved')
                    //     ->required(),

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.first_name')
                    ->searchable()
                    ->sortable()
                    ->default('NA')
                    ->label('Vendor Name'),
                Tables\Columns\TextColumn::make('tl_number')
                    ->searchable()
                    ->default('NA')
                    ->label('TL Number'),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->default('pending')
                    ->label('Status')
                    ->hidden(function () {
                        return Role::where('id', auth()->user()?->role_id)->first()?->name == 'Admin';
                    })
                    ->getStateUsing(function ($record) {
                        $ownerAssociation = $record->ownerAssociation()
                            ->wherePivot('owner_association_id', Filament::getTenant()?->id)
                            ->first();
                        return $ownerAssociation ? $ownerAssociation->pivot->status : 'NA';
                    }),
                TextColumn::make('remarks')
                    ->searchable()
                    ->limit(30)
                    ->default('NA')
                    ->label('Remarks')
                    ->hidden(function () {
                        return Role::where('id', auth()->user()?->role_id)->first()?->name == 'Admin';
                    })
                    ->getStateUsing(function ($record) {
                        $ownerAssociation = $record->ownerAssociation()
                            ->wherePivot('owner_association_id', Filament::getTenant()?->id)
                            ->first();
                        return $ownerAssociation ? $ownerAssociation->pivot->remarks : 'NA';
                    }),
                ViewColumn::make('Services')->view('tables.columns.vendor-service'),
                ViewColumn::make('Documents')->view('tables.columns.vendordocument'),
                ViewColumn::make('Managers')->view('tables.columns.vendormanager'),

            ])
            ->filters([
                Filter::make('status')
                    ->form([
                        Select::make('status')
                            ->options([
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'NA' => 'Pending'
                            ])
                            ->label('Status')
                            ->placeholder('Select Status'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['status'])) {
                            // Handling 'Pending' (NA) case
                            if ($data['status'] === 'NA') {
                                $query->whereHas('ownerAssociation', function ($query) {
                                    $query->where(function ($query) {
                                        $query->whereNull('owner_association_vendor.status') // Fetch records where status is null
                                            ->orWhereNotIn('owner_association_vendor.status', ['approved', 'rejected']); // Fetch records where status is neither approved nor rejected
                                    })
                                        ->where('owner_association_vendor.owner_association_id', Filament::getTenant()?->id);
                                });
                            } else {
                                // Otherwise, fetch records based on the selected status (approved/rejected)
                                $query->whereHas('ownerAssociation', function ($query) use ($data) {
                                    $query->where('owner_association_vendor.status', $data['status'])
                                        ->where('owner_association_vendor.owner_association_id', Filament::getTenant()?->id);
                                });
                            }
                        }
                        return $query;
                    }),

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
                //Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            VendorResource\RelationManagers\ServicesRelationManager::class,
            // VendorResource\RelationManagers\UsersRelationManager::class,
            // VendorResource\RelationManagers\ContactsRelationManager::class,
            VendorResource\RelationManagers\DocumentsRelationManager::class,
            // VendorResource\RelationManagers\BuildingsRelationManager::class,
            BuildingvendorRelationManager::class,
            VendorResource\RelationManagers\TechnicianVendorsRelationManager::class,
            VendorResource\RelationManagers\ManagersRelationManager::class,
            VendorResource\RelationManagers\EscalationMatrixRelationManager::class,
            VendorResource\RelationManagers\ContractsRelationManager::class,
            VendorResource\RelationManagers\WdasRelationManager::class,
            VendorResource\RelationManagers\InvoicesRelationManager::class,
            VendorResource\RelationManagers\AssetsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendors::route('/'),
            //'create' => Pages\CreateVendor::route('/create'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
            'view' => Pages\ViewVendor::route('/{record}'),
        ];
    }
}
