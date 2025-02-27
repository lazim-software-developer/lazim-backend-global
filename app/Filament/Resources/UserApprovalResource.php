<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\FlatOwner;
use App\Models\FlatOwners;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\UserApproval;
use App\Models\Building\Flat;
use App\Models\ApartmentOwner;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserApprovalResource\Pages;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\UserApprovalResource\RelationManagers;
use App\Filament\Resources\UserApprovalResource\RelationManagers\HistoryRelationManager;

class UserApprovalResource extends Resource
{
    protected static ?string $model = UserApproval::class;
    protected static ?string $modelLabel = 'Resident Approval';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
    ->schema([
        Section::make('User Information')
            ->schema([
                TextInput::make('user')->disabledOn('edit'),
                TextInput::make('email')->disabledOn('edit'),
                TextInput::make('phone')->disabledOn('edit'),
                DateTimePicker::make('created_at')
                    ->label('Date of Creation')
                    ->disabled(),
            ])
            ->columns(2),
        Section::make('Flat & Building Details')
            ->schema([
                TextInput::make('building')
                ->formatStateUsing(function($record){
                    return Flat::where('id', $record->flat_id)->first()?->building->name;
                })
                ->disabled(),
                Select::make('flat_id')->label('Flat')
                    ->relationship('flat', 'property_number')
                    ->disabled()
                    ->live(),
            ])
            ->columns(2),
        Section::make('Documents')
            ->schema([
                FileUpload::make('document')
                    ->label(function (Get $get) {
                        if($get('document_type') == 'Ejari'){
                            return 'Tenancy Contract / Ejari';
                        }
                        return $get('document_type');
                    })
                    ->disk('s3')
                    ->directory('dev')
                    ->openable(true)
                    ->downloadable(true)
                    ->required()
                    ->disabled(),
                FileUpload::make('emirates_document')
                    ->disk('s3')
                    ->directory('dev')
                    ->openable(true)
                    ->downloadable(true)
                    ->required()
                    ->disabled(),
                FileUpload::make('passport')
                    ->disk('s3')
                    ->directory('dev')
                    ->openable(true)
                    ->downloadable(true)
                    ->required(function(UserApproval $record){
                        if ($record->ownerAssociation?->resource == 'Default'){
                            return false;
                        }
                        return true;
                    })
                    ->disabled(),
            ])
            ->columns(3),
            Section::make('Owners Information')
            ->schema([
                Tabs::make('Owners')
                    ->tabs(function ($record) {
                        // Early return if no record
                        if (!$record || !$record->flat_id) {
                            return [
                                Tabs\Tab::make('no_data')
                                    ->label('No Data')
                                    ->schema([
                                        Placeholder::make('no_data')
                                            ->content('No owner data available.')
                                    ])
                            ];
                        }
                        
                        // Get all flat owners associated with this flat
                        $flatOwners = FlatOwners::where('flat_id', $record->flat_id)->get();
                        
                        if ($flatOwners->isEmpty()) {
                            return [
                                Tabs\Tab::make('no_owners')
                                    ->label('No Owners')
                                    ->schema([
                                        Placeholder::make('')
                                            ->content('No owners found for this flat.')
                                    ])
                            ];
                        }
                        
                        $tabs = [];
                        
                        // Create a tab for each owner
                        foreach ($flatOwners as $index => $flatOwner) {
                            $ownerDetail = ApartmentOwner::where('id', $flatOwner->owner_id)->first();
                            
                            if ($ownerDetail) {
                                $tabs[] = Tabs\Tab::make("owner_{$index}")
                                    ->label($ownerDetail->name ?? "Owner " . ($index + 1))
                                    ->schema([
                                        Placeholder::make("owner_{$index}_name")
                                            ->label('Name')
                                            ->content($ownerDetail->name ?? 'N/A'),
                                        Placeholder::make("owner_{$index}_email")
                                            ->label('Email')
                                            ->content($ownerDetail->email ?? 'N/A'),
                                        Placeholder::make("owner_{$index}_phone")
                                            ->label('Phone')
                                            ->content($ownerDetail->mobile ?? 'N/A'),
                                        Placeholder::make("owner_{$index}_passport")
                                            ->label('Passport')
                                            ->content($ownerDetail->passport ?? 'N/A'),
                                        Placeholder::make("owner_{$index}_emirates_id")
                                            ->label('Emirates ID')
                                            ->content($ownerDetail->emirates_id ?? 'N/A'),
                                        Placeholder::make("owner_{$index}_trade_license")
                                            ->label('Trade License')
                                            ->content($ownerDetail->trade_license ?? 'N/A'),
                                    ])
                                    ->columns(2);
                            }
                        }
                        
                        return $tabs;
                    })
            ]),
        Section::make('Approval Details')
            ->schema([
                Grid::make(2)->schema([
                Select::make('status')
                    ->options([
                        'approved' => 'Approve',
                        'rejected' => 'Reject',
                    ])
                    ->disabled(function (UserApproval $record) {
                        return $record->status != null;
                    })
                    ->searchable()
                    ->live()
                    ->required()->columnSpan(1),
                ]),
                Textarea::make('remarks')
                    ->maxLength(250)
                    ->rows(5)
                    ->required()
                    ->visible(function (Get $get) {
                        if ($get('status') == 'rejected') {
                            return true;
                        }
                        return false;
                    })->columnSpan(1),
            ])->columns(2),
    ]);

    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.first_name')
                    ->numeric()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->default('NA')->formatStateUsing(fn ($state) => ucwords($state)),
                Tables\Columns\TextColumn::make('flat.building.name')->label('Building')->default('NA'),
                Tables\Columns\TextColumn::make('flat.property_number')->label('Flat')->default('NA'),
                Tables\Columns\TextColumn::make('created_at')->label('Date of creation')->default('NA')
            ])
            ->filters([
                Filter::make('filter')
                    ->form([
                        Select::make('building_id')
                            ->options(function () {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return Building::all()->pluck('name', 'id');
                                } else {
                                    return Building::where('owner_association_id', auth()->user()?->owner_association_id)
                                        ->pluck('name', 'id');
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->label('Building')
                            ->reactive(),
                        Select::make('flat_id')
                            ->label('Flat')
                            ->options(function (callable $get) {
                                if (empty($get('building_id'))) {
                                    return [];
                                } else {
                                    return Flat::where('building_id', $get('building_id'))
                                        ->pluck('property_number', 'id');
                                }
                            })
                            ->searchable(),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['building_id']) && $data['building_id']) {
                            $query->whereHas('flat', function ($q) use ($data) {
                                $q->where('building_id', $data['building_id']);
                            });
                        }
            
                        if (isset($data['flat_id']) && $data['flat_id']) {
                            $query->where('flat_id', $data['flat_id']);
                        }
                    }),
            ])
            ->filtersFormColumns(3)            
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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

    public static function getRelations(): array
    {
        return [
            HistoryRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserApprovals::route('/'),
            'create' => Pages\CreateUserApproval::route('/create'),
            'view' => Pages\ViewUserApproval::route('/{record}'),
            'edit' => Pages\EditUserApproval::route('/{record}/edit'),
        ];
    }
}
