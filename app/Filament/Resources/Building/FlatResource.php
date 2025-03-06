<?php
namespace App\Filament\Resources\Building;

use Filament\Forms;
use App\Filament\Resources\Building\FlatResource\Pages;
use App\Filament\Resources\Building\FlatResource\RelationManagers\DocumentsRelationManager;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\BelongsToSelect;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\FlatResource\Pages\ViewFlat;
use App\Filament\Resources\Building\FlatResource\RelationManagers;
use Illuminate\Support\Facades\DB;

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
                            ->default('NA')->placeholder('NA'),
                        Toggle::make('status')
                            ->rules(['boolean'])
                            ->label('Status'),
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
