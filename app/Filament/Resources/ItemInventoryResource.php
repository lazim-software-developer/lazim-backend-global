<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use App\Models\Item;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ItemInventory;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ItemInventoryResource\Pages;
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
                            return Item::whereIn('building_id', Building::where('owner_association_id', auth()->user()->owner_association_id)->pluck('id'))->pluck('name', 'id');
                        })
                        ->required()
                        ->searchable(),
                    DateTimePicker::make('date')
                        ->rules(['date'])
                        ->required()
                        ->minDate(now())
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
                                if(Item::find($get('item_id'))->quantity == 0){
                                    $fail('You cannot use the Item '.Item::find($get('item_id'))->name . ' because the quantity is Zero.');
                                }
                                if ($get('type') == 'used' && Item::find($get('item_id'))->quantity < $value) {
                                    $fail('The quantity value must be less than are equal to ' . Item::find($get('item_id'))->quantity . '.');
                                }
                            };
                        },])
                        ->required()
                        ->integer()
                        ->minValue(1),
                    Textarea::make('comments')
                        ->rules(['max:100', 'regex:/^(?=.*[a-zA-Z])[a-zA-Z0-9\s!@#$%^&*_+\-=,.]*$/'])
                        ->required(),
                    Select::make('user_id')
                        ->relationship('user', 'first_name')
                        ->searchable()
                        ->preload()
                        ->required(),

                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup('item.name')
            ->columns([
                TextColumn::make('item.name')
                    ->searchable(),
                TextColumn::make('date'),
                TextColumn::make('type')->searchable(),
                TextColumn::make('quantity')
                    ->searchable(),
                TextColumn::make('user.first_name')
                    ->searchable(),
                TextColumn::make('comments')
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
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
            // 'edit' => Pages\EditItemInventory::route('/{record}/edit'),
        ];
    }
}
