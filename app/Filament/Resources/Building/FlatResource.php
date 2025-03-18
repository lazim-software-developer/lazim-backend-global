<?php
namespace App\Filament\Resources\Building;

use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\Building\Flat;
use App\Models\OwnerAssociation;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use pxlrbt\FilamentExcel\Columns\Column;
use Filament\Tables\Filters\SelectFilter;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use App\Filament\Resources\Building\FlatResource\Pages;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\Building\FlatResource\RelationManagers\DocumentsRelationManager;

class FlatResource extends Resource
{
    protected static ?string $model = Flat::class;

    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel      = 'Units';
    protected static ?string $navigationGroup = 'Flat Management';

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
                        TextInput::make('floor')->label('Unit')
                            ->required()
                            ->placeholder('Floor'),
                        Select::make('owner_association_id')
                            ->label('Owner Association')
                            ->preload()
                            ->searchable()
                            // ->relationship('ownerAssociation', 'name')
                            ->required()
                            ->options(function () {
                                if(auth()->user()?->role?->name === 'Property Manager'){
                                    return OwnerAssociation::where('role', auth()->user()?->role?->name)->pluck('name', 'id');
                                }
                                return OwnerAssociation::pluck('name', 'id');
                            })
                            ->placeholder('Select an Owner Association'),
                        TextInput::make('property_number')
                            ->label('Unit Number')
                            ->required()
                            ->unique(
                                Flat::class,
                                'property_number',
                                ignoreRecord: true,
                                modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule, Get $get) {
                                    return $rule->where('building_id', $get('building_id'));
                                }
                            )
                            ->validationMessages([
                                'unique' => 'Unit Number already exists in the selected building.',
                            ])
                            ->regex('/^[\w\-\s]+$/')
                            ->placeholder('Unit Number'),
                        TextInput::make('property_type')->label('Property')
                            ->required()
                            ->placeholder('Property'),
                        Select::make('building_id')
                            ->rules(['exists:buildings,id'])
                            ->relationship('building', 'name')
                            ->reactive()
                            ->preload()
                            ->searchable()
                            ->placeholder('Building'),
                        TextInput::make('suit_area')
                            ->default('NA')->placeholder('NA'),
                        TextInput::make('actual_area')
                            ->default('NA')->placeholder('NA'),
                        TextInput::make('balcony_area')
                            ->default('NA')->placeholder('NA'),
                        TextInput::make('applicable_area')
                            ->default('NA')->placeholder('NA'),
                        TextInput::make('virtual_account_number')
                            ->default('NA')->placeholder('NA'),
                        TextInput::make('parking_count')
                            ->default('NA')->placeholder('NA'),
                        TextInput::make('plot_number')
                            ->placeholder('NA')
                            ->label('Plot Number')
                            ->rule('regex:/^[0-9\-.,\/_ ]+$/'),
                        TextInput::make('makhani_number')
                            ->placeholder('NA')
                            ->label('Makani Number')
                            ->rule('regex:/^[0-9\-.,\/_ ]+$/'),
                        TextInput::make('dewa_number')
                            ->placeholder('NA')
                            ->label('DEWA Number')
                            ->rule('regex:/^[0-9\-.,\/_ ]+$/'),
                        TextInput::make('etisalat/du_number')
                            ->label('DU/Etisalat Number')
                            ->placeholder('NA')
                            ->rule('regex:/^[0-9\-.,\/_ ]+$/'),
                        TextInput::make('btu/ac_number')
                            ->placeholder('NA')
                            ->label('BTU/AC Number')
                            ->rule('regex:/^[0-9\-.,\/_ ]+$/'),
                        TextInput::make('lpg_number')
                            ->placeholder('NA')
                            ->label('LPG Number')
                            ->rule('regex:/^[0-9\-.,\/_ ]+$/'),
                        Hidden::make('resource')
                            ->default('Lazim'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                TextColumn::make('floor')
                    ->default('NA')
                    ->searchable()
                    ->label('Flat'),
                TextColumn::make('property_number')
                    ->default('NA')
                    ->searchable()
                    ->label('Unit Number'),
                TextColumn::make('building.name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('suit_area')
                    ->formatStateUsing(fn($record) => is_numeric($record->suit_area)
                        ? number_format((float) $record->suit_area, 2)
                        : 'NA')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('actual_area')
                    ->formatStateUsing(fn($record) => is_numeric($record->actual_area)
                        ? number_format((float) $record->actual_area, 2)
                        : 'NA')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('balcony_area')
                    ->formatStateUsing(fn($record) => is_numeric($record->balcony_area)
                        ? number_format((float) $record->balcony_area, 2)
                        : 'NA')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('applicable_area')
                    ->formatStateUsing(fn($record) => is_numeric($record->applicable_area)
                        ? number_format((float) $record->applicable_area, 2)
                        : 'NA')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('virtual_account_number')
                    ->default('NA')
                    ->searchable()
                    ->visible(! in_array(auth()->user()->role->name, ['Property Manager', 'Admin']))
                    ->limit(50),
                TextColumn::make('parking_count')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('plot_number')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('tenants.role')
                    ->label('Occupied By')
                    ->default('NA'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return Building::all()->pluck('name', 'id');
                        } elseif (Role::where('id', auth()->user()->role_id)->first()->name == 'Property Manager'
                        || OwnerAssociation::where('id', auth()->user()?->owner_association_id)
                                ->pluck('role')[0] == 'Property Manager') {
                            $buildings = DB::table('building_owner_association')
                                ->where('owner_association_id', auth()->user()->owner_association_id)
                                ->where('active', true)
                                ->pluck('building_id');
                            return Building::whereIn('id', $buildings)->pluck('name', 'id');

                        } else {
                            return Building::where('owner_association_id', auth()->user()?->owner_association_id)
                                ->pluck('name', 'id');
                        }
                    })
                    ->searchable()
                    ->label('Building')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                Action::make('delete')
                    ->button()
                    ->action(function ($record,) {
                        $record->delete();

                        Notification::make()
                            ->title('Flat Deleted Successfully')
                            ->success()
                            ->send()
                            ->duration('4000');
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Are you sure you want to delete this ?')
                    ->modalButton('Delete'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                Action::make('delete')
                    ->button()
                    ->visible(function () {
                        $auth_user = auth()->user();
                        $role      = Role::where('id', $auth_user->role_id)->first()?->name;

                        if ($role === 'Admin' || $role === 'Property Manager') {
                            return true;
                        }
                    })
                    ->action(function ($record,) {
                        if(!empty($record->owner_association_id)) {
                            DB::table('property_manager_flats')
                            ->where('flat_id', $record->id)
                            ->where('owner_association_id', $record->owner_association_id)
                            ->delete(); 
                        }else{
                            DB::table('property_manager_flats')
                            ->where('flat_id', $record->id)
                            ->delete(); 
                        }
                        $record->delete();

                        Notification::make()
                            ->title('Flat Deleted Successfully')
                            ->success()
                            ->send()
                            ->duration('4000');
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Are you sure you want to delete this ?')
                    ->modalButton('Delete'),
            ])
            ->bulkActions([
                ExportBulkAction::make()
                ->exports([
                    ExcelExport::make()
                        ->withColumns([
                            // Column::make('created_by')
                            // ->heading('Created By')
                            // ->formatStateUsing(fn ($record) => 
                            //     $record->CreatedBy->first_name.' '.$record->CreatedBy->last_name ?? 'N/A'
                            // ), 
                            // Custom column using relationship
                            Column::make('owner_association_id')
                            ->heading('Owner Association Name')
                            ->formatStateUsing(fn ($record) => 
                                $record->ownerAssociation->name ?? 'N/A'
                            ), 
                            Column::make('building_id')
                            ->heading('Building Name')
                            ->formatStateUsing(fn ($record) => 
                                $record->building->name ?? 'N/A'
                            ), 
                            Column::make('floor')
                                ->heading('Floor'),
                            Column::make('property_number')
                                ->heading('Property Number'),
                            Column::make('property_type')
                                ->heading('Property Type'),
                            Column::make('suit_area')
                                ->heading('Suit Area'),
                            Column::make('actual_area')
                            ->heading('Actual Area'),
                            Column::make('actual_area')
                                ->heading('Actual Area'),
                            Column::make('balcony_area')
                            ->heading('Balcony Area'),
                            Column::make('applicable_area')
                            ->heading('Applicable Area'),
                            Column::make('virtual_account_number')
                            ->heading('Virtual Account Number'),
                            Column::make('parking_count')
                            ->heading('Parking Count'),
                            Column::make('plot_number')
                            ->heading('Plot Number'), 
                            Column::make('dewa_number')
                            ->heading('DEWA Number'), 
                            Column::make('makhani_number')
                            ->heading('Makhani Number'),
                            Column::make('etisalat/du_number')
                            ->heading('Etisalat/DU Number'),
                            Column::make('btu/ac_number')
                            ->heading('BTU/AC Number'), 
                            Column::make('lpg_number')
                            ->heading('LPG Number'), 
                            Column::make('resource')
                            ->heading('Resource'),               
                            // Formatted date with custom accessor
                            Column::make('created_at')
                                ->heading('Created Date')
                                ->formatStateUsing(fn ($state) => 
                                    $state ? $state->format('d/m/Y') : ''
                                ),
                                // Column::make('status')
                                // ->heading('Status')
                                // ->formatStateUsing(fn ($record) => 
                                //     $record->status == 1
                                //         ? 'Active' 
                                //         : 'Inactive'
                                // ),
                                
                            // Created by user info
                            // Column::make('created_by_name')
                            //     ->heading('Created By')
                            //     ->formatStateUsing(fn ($record) => 
                            //         $record->createdBy->name ?? 'System'
                            //     ),
                        ])
                        ->withFilename(date('Y-m-d') . '-flat-report')
                        ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                ]),
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
        if (auth()->user()?->role?->name === 'Property Manager') {

            return [
                // FlatResource\RelationManagers\FlatDomesticHelpRelationManager::class,
                // FlatResource\RelationManagers\FlatTenantRelationManager::class,
                // FlatResource\RelationManagers\FlatVisitorRelationManager::class,
                // FlatResource\RelationManagers\UserRelationManager::class,
                DocumentsRelationManager::class,
            ];
        }
        return [];
    }

    public static function getPages(): array
    {
        return [
            'create' => Pages\CreateFlat::route('/create'),
            'index' => Pages\ListFlats::route('/'),
            'view' => ViewFlat::route('/{record}'),
            'edit' => Pages\EditFlat::route('/{record}/edit'),
        ];
    }
}
