<?php

namespace App\Filament\Resources\Vendor\VendorResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Asset;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Service;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class AssetsRelationManager extends RelationManager
{
    protected static string $relationship = 'assets';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->label('Asset Name'),
                TextColumn::make('description')->searchable()->label('Description'),
                TextColumn::make('location')->label('Location'),
                TextColumn::make('service.name')->searchable()->label('Service'),
                TextColumn::make('building.name')->searchable()->label('Building Name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                ->label('Add')
                ->recordSelect(function (RelationManager $livewire) {
                    $vendorId = $livewire->ownerRecord->id;

                    // Get all the Assets
                    $allAssets = Asset::all()->pluck('id')->toArray();
                    $existingAssets =  DB::table('asset_vendor')
                        ->where('vendor_id', $vendorId)
                        ->whereIn('asset_id', $allAssets)->pluck('asset_id')->toArray();
                    $notSelected = Asset::all()->whereNotIn('id', $existingAssets)->pluck('name', 'id')->toArray();
                    return Select::make('recordId')
                        ->label('Assets')
                        ->options($notSelected)
                        ->searchable()
                        ->required()
                        ->preload();
                }),
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make()->label('Remove'),
                //Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DetachBulkAction::make(),
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                //Tables\Actions\CreateAction::make(),
                //Tables\Actions\AttachAction::make(),
            ]);
    }
}
