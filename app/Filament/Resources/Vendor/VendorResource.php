<?php

namespace App\Filament\Resources\Vendor;

use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\Vendor\Vendor;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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
                                ->default(auth()->user()->owner_association_id),
                            Select::make('owner_id')
                                ->label('Vendor Name')
                                ->rules(['exists:users,id'])
                                ->required()
                                ->disabled()
                                ->preload()
                                ->relationship('user', 'first_name')
                                ->searchable()
                                ->getSearchResultsUsing(fn(string $search): array => User::where('role_id', 2, "%{$search}%")->limit(50)->pluck('first_name', 'id')->toArray())
                                ->getOptionLabelUsing(fn($value): ?string => User::find($value)?->first_name)
                                ->placeholder('Vendor Name'),
                            TextInput::make('tl_number')->label('Trade Lisence Number')
                                ->rules(['max:50', 'string'])
                                ->disabled()
                                ->required()
                                ->unique(
                                    'vendors',
                                    'tl_number',
                                    fn(?Model $record) => $record
                                )
                                ->placeholder('Trade Lisence Number'),

                            DatePicker::make('tl_expiry')
                                ->label('Trade Licence Expiry')
                                ->rules(['date'])
                                ->disabled()
                                ->required()
                                ->placeholder('Trade Lisence Expiry'),
                            TextInput::make('address_line_1')
                                ->placeholder('NA')
                                ->disabled()
                                ->label('Address Line 1'),
                            TextInput::make('address_line_2')
                                ->placeholder('NA')
                                ->disabled()
                                ->label('Address Line 2'),
                            TextInput::make('landline_number')
                                ->placeholder('NA')
                                ->disabled()
                                ->label('Landline Number'),
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
                                ])
                                ->visible(fn($record) => $record->status === null && $record->documents()->count() > 0 && $record->services()->count() > 0 && $record->managers()->count() > 0)
                                ->searchable()
                                ->live(),
                            TextInput::make('remarks')
                                ->rules(['max:255'])
                                ->required()
                                ->visible(function (callable $get) {
                                    if ($get('status') == 'rejected') {
                                        return true;
                                    }
                                    return false;
                                })
                                ->disabled(fn($record) => $record->status !== null ),
                            Toggle::make('active')
                                ->rules(['boolean'])
                                ->required()
                                ->visible(fn($record) => $record->status === 'approved')
                                ->inline(false)
                                ->label('Active'),
                            TextInput::make('remarks')
                                ->rules(['max:150'])
                                ->visible(fn($record) => $record->status === 'approved')
                                ->required(),

                        ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('user.first_name')
                    ->searchable()
                    ->default('NA')
                    ->label('Vendor Name'),
                Tables\Columns\TextColumn::make('tl_number')
                    ->searchable()
                    ->default('NA')
                    ->label('TL Number'),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->default('NA')
                    ->label('Status'),
                TextColumn::make('remarks')
                    ->searchable()
                    ->default('NA')
                    ->label('Remarks'),
                ViewColumn::make('Services')->view('tables.columns.vendor-service'),
                ViewColumn::make('Documents')->view('tables.columns.vendordocument'),
                ViewColumn::make('Managers')->view('tables.columns.vendormanager'),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('vendorByBuilding')
                ->form([
                    Select::make('Building')
                    ->searchable()
                    ->options(function () {
                        return Building::all()->pluck('name', 'id');
                        // Optionally, add a condition to filter buildings based on certain criteria.
                    }),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query->when(
                        isset($data['Building']),
                        function ($query) use ($data) {
                            $query->whereHas('buildings', function ($query) use ($data) {
                                $query->where('buildings.id', $data['Building']);
                            });
                        }
                    );
                })

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
            ]);
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
