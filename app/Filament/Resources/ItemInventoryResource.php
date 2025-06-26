<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use App\Models\Item;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\ItemInventory;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ItemInventoryResource\Pages;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\ItemInventoryResource\RelationManagers;

class ItemInventoryResource extends Resource
{
    protected static ?string $model = ItemInventory::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Item inventory';
    protected static ?string $navigationGroup = 'Inventory Management';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([
                    Select::make('item_id')
                        ->relationship('item', 'name')
                        ->preload()
                        ->options(function () {
                            if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                return Item::pluck('name', 'id');
                            }
                            return Item::whereIn('building_id', Building::where('owner_association_id', auth()->user()?->owner_association_id)->pluck('id'))->pluck('name', 'id');
                        })
                        ->required()
                        ->searchable(),
                    DateTimePicker::make('date')
                        ->rules(['date'])
                        ->required()
                        // ->minDate(now())
                        ->displayFormat('d-M-Y h:i A'),
                    Select::make('type')
                        ->options([
                            'incoming' => 'Incoming',
                            'used' => 'Used',
                        ])
                        ->required()
                        ->preload()
                        ->searchable(),
                    TextInput::make('quantity')
                        ->rules([function (Get $get) {
                            return function (string $attribute, $value, Closure $fail) use ($get) {
                                if ($get('type') == 'used' && Item::find($get('item_id'))->quantity == 0) {
                                    $fail('You cannot use the Item ' . Item::find($get('item_id'))->name . ' because the quantity is Zero.');
                                }
                                if ($get('type') == 'used' && Item::find($get('item_id'))->quantity < $value) {
                                    $fail('The quantity value must be less than are equal to available quantity:' . Item::find($get('item_id'))->quantity . '.');
                                }
                            };
                        },])
                        ->required()
                        ->integer()
                        ->minValue(1)
                        ->maxValue(100000),
                    Textarea::make('comments')
                        ->rules(['max:100', 'regex:/^(?=.*[a-zA-Z])[a-zA-Z0-9\s!@#$%^&*_+\-=,.]*$/'])
                        ->required(),
                    Hidden::make('user_id')
                        ->default(auth()->user()->id),

                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        $buildings = Building::where('owner_association_id', auth()->user()?->owner_association_id)->pluck('id');
        $items = Item::whereIn('building_id', $buildings)->pluck('id');
        return $table
            // ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('item_id', $items)->orderBy('created_at','desc')->withoutGlobalScopes())
            ->defaultGroup('item.name')
            ->columns([
                TextColumn::make('item.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('date'),
                TextColumn::make('type')->searchable()->formatStateUsing(fn($state) => ucfirst($state)),
                TextColumn::make('quantity')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.first_name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('comments')
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('item_id')
                    ->label('Item')
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return Item::pluck('name', 'id');
                        } else {
                            return Item::where('owner_association_id', auth()->user()->owner_association_id)->pluck('name', 'id');
                        }
                    })
                    ->searchable()
                    ->preload()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListItemInventories::route('/'),
            'create' => Pages\CreateItemInventory::route('/create'),
            'view' => Pages\ViewItemInventory::route('/{record}'),
            'edit' => Pages\EditItemInventory::route('/{record}/edit'),
        ];
    }
}
