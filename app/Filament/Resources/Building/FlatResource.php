<?php

namespace App\Filament\Resources\Building;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\Building\Flat;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use pxlrbt\FilamentExcel\Columns\Column;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\MarkdownEditor;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Filament\Forms\Components\BelongsToSelect;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Building\FlatResource\Pages;
use App\Filament\Resources\FlatResource\Pages\ViewFlat;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\Building\FlatResource\RelationManagers;

class FlatResource extends Resource
{
    protected static ?string $model = Flat::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Units';
    protected static ?string $navigationGroup = 'Flat Management';
    protected static ?string $tenantRelationshipName = 'flats';
    public static function getSlug(): string
    {
        return 'flats';
    }


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
                        Hidden::make('owner_association_id')
                            ->default(auth()->user()?->owner_association_id),
                        TextInput::make('property_number')->label('Unit')
                            ->required()
                            ->placeholder('Unit Number'),
                        TextInput::make('property_type')->label('Property')
                            ->required()
                            ->placeholder('Property'),
                        Select::make('building_id')
                            ->rules(['exists:buildings,id'])
                            ->relationship('building', 'name', function ($query) {
                                $query->where('owner_association_id', auth()->user()->owner_association_id)->where('resource', 'Default');
                            })
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
                        ->placeholder('Parking Count')
                        ->numeric()
                        ->rules([
                            'numeric',
                            'min:0',
                            'integer',
                            'regex:/^[0-9]+$/'
                        ])
                        ->minValue(0)
                        ->maxValue(999),
                        TextInput::make('plot_number')
                        ->placeholder('Plot Number')
                        ->numeric()
                        ->rules([
                            'numeric',
                            'min:0',
                            'integer',
                            'regex:/^[0-9]+$/'
                        ])
                        ->minValue(0)
                        ->maxValue(999),
                        Toggle::make('status')
                            ->rules(['boolean'])
                            ->label('Status'),
                        Hidden::make('created_by')
                        ->default(auth()->user()?->id),
                        Hidden::make('updated_by')
                        ->default(auth()->user()?->id),
                        MarkdownEditor::make('description')
                            ->toolbarButtons([
                                'bold',
                                'bulletList',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'undo',
                            ])
                            ->label('About')
                            ->columnSpanFull(),
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
                    ->formatStateUsing(function($record) {
                        return $record->suit_area === 'NA' ? 'NA' : number_format($record->suit_area, 2);
                    })
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('actual_area')
                    ->formatStateUsing(function($record) {
                        return $record->actual_area === 'NA' ? 'NA' : number_format($record->actual_area, 2);
                    })
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('balcony_area')
                    ->formatStateUsing(function($record) {
                        return $record->balcony_area === 'NA' ? 'NA' : number_format($record->balcony_area, 2);
                    })
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('applicable_area')
                    ->formatStateUsing(function($record) {
                        return $record->applicable_area === 'NA' ? 'NA' : number_format($record->applicable_area, 2);
                    })
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('virtual_account_number')
                    ->default('NA')
                    ->searchable()
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
                    ->default('NA')
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return Building::all()->pluck('name', 'id');
                        } else {
                            return Building::where('owner_association_id', auth()->user()?->owner_association_id)
                                ->pluck('name', 'id');
                        }
                    })
                    ->searchable()
                    ->label('Building')
                    ->preload()
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
            ->bulkActions([
                ExportBulkAction::make()
                ->exports([
                    ExcelExport::make()
                        ->withColumns([
                            Column::make('created_by')
                            ->heading('Created By')
                            ->formatStateUsing(fn ($record) => 
                                $record->CreatedBy->first_name.' '.$record->CreatedBy->last_name ?? 'N/A'
                            ), 
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
                            // Formatted date with custom accessor
                            Column::make('created_at')
                                ->heading('Created Date')
                                ->formatStateUsing(fn ($state) => 
                                    $state ? $state->format('d/m/Y') : ''
                                ),
                                Column::make('status')
                                ->heading('Status')
                                ->formatStateUsing(fn ($record) => 
                                    $record->status == 1
                                        ? 'Active' 
                                        : 'Inactive'
                                ),
                                
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
        return [
            // FlatResource\RelationManagers\FlatDomesticHelpRelationManager::class,
            // FlatResource\RelationManagers\FlatTenantRelationManager::class,
            // FlatResource\RelationManagers\FlatVisitorRelationManager::class,
            // FlatResource\RelationManagers\UserRelationManager::class,
        ];
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
