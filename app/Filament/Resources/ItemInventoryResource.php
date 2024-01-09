<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Item;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ItemInventory;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
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
    protected static ?string $modelLabel = 'Item inventorys';
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
                            return Item::whereIn('building_id', Building::where('owner_association_id', auth()->user()->owner_association_id)->pluck('id'))->pluck('name','id');
                        })
                        ->searchable(),
                    DateTimePicker::make('date')
                        ->rules(['date'])
                        ->minDate(now())
                        ->displayFormat('d-M-Y h:i A'),
                    Select::make('type')
                        ->options([
                            'incoming' => 'Incoming',
                            'used' => 'Used',
                        ])
                        ->preload()
                        ->searchable(),
                    TextInput::make('quantity')
                        ->required()
                        ->integer()
                        ->minValue(1),
                    Textarea::make('comments')
                        ->rules(['max:100', 'regex:/^(?=.*[a-zA-Z])[a-zA-Z0-9\s!@#$%^&*_+\-=,.]*$/']),
                    Select::make('user_id')
                        ->relationship('user','first_name')
                        ->searchable()
                        ->preload(),

                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
            ->filters([
                //
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
            'edit' => Pages\EditItemInventory::route('/{record}/edit'),
        ];
    }
}
