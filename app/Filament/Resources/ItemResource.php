<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use App\Models\Item;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ItemResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Models\Vendor\Vendor;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
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
                    Select::make('building_id')
                        ->relationship('building', 'name')
                        ->preload()
                        ->disabledOn('edit')
                        ->required()
                        ->live()
                        ->options(function () {
                            return Building::where('owner_association_id', auth()->user()->owner_association_id)->pluck('name', 'id');
                        })
                        ->searchable(),
                    TextInput::make('name')
                        ->required()
                        ->disabledOn('edit')
                        ->rules([
                            'max:50',
                            'regex:/^[a-zA-Z\s]*$/',
                            fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                if (Item::where('building_id', $get('building_id'))->where('name', $value)->exists()) {
                                    $fail('The Name is already taken for this Building.');
                                }
                            },
                        ]),
                    TextInput::make('quantity')
                        ->required()
                        ->integer()
                        ->disabledOn('edit')
                        ->minValue(0)
                        ->maxValue(100000),
                    Textarea::make('description')
                        ->rules(['max:100', 'regex:/^(?=.*[a-zA-Z])[a-zA-Z0-9\s!@#$%^&*_+\-=,.]*$/'])
                        ->required()
                        ->disabledOn('edit'),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        $buildings = Building::where('owner_association_id',auth()->user()->owner_association_id)->pluck('id');
        return $table->modifyQueryUsing(fn(Builder $query) => $query->whereIn('building_id', $buildings)->orderBy('created_at','desc')->withoutGlobalScopes())
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->searchable(),
                TextColumn::make('building.name')
                    ->searchable(),
                TextColumn::make('vendors.name')
                    ->searchable(),
                TextColumn::make('description')
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
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('attach')
                    ->form([
                        Select::make('vendor_id')
                        ->required()
                        ->relationship('vendors', 'name')
                        ->options(function () {
                            $oaId = auth()->user()->owner_association_id;
                            return Vendor::where('owner_association_id', $oaId)->where('status', 'approved')
                                ->pluck('name', 'id');
                        })
                        ])
                        ->action(function (Collection $records,array $data){
                            $vendorId= $data['vendor_id'];
                            // dd($records,$vendorId);
                            foreach($records as $record){
                                // dd($record->vendors()->syncWithoutDetaching([$vendorId]));
                                $record->vendors()->sync([$vendorId]);
                            }
                            Notification::make()
                            ->title("Vendor attached successfully")
                            ->success()
                            ->send();
                        })->label('Attach Vendor')
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
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'view' => Pages\ViewItem::route('/{record}'),
            // 'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}
