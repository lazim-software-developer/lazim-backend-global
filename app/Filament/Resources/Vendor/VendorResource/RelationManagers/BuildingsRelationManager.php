<?php

namespace App\Filament\Resources\Vendor\VendorResource\RelationManagers;

use App\Models\Building\Building;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class BuildingsRelationManager extends RelationManager
{
    protected static string $relationship = 'buildings';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('active')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->limit(50),
                Tables\Columns\IconColumn::make('active')->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
                // Tables\Actions\AttachAction::make()
                // ->label('Add')
                //     ->recordSelect(function (RelationManager $livewire) {
                //         $vendorId = $livewire->ownerRecord->id;
                        
                //         // Get all the Buildings
                //         $allBuildings = Building::all()->pluck('id')->toArray();
                //         $existingServices =  DB::table('building_vendor')
                //             ->where('vendor_id', $vendorId)
                //             ->whereIn('building_id', $allBuildings)->pluck('building_id')->toArray();
                //         $notSelected = Building::all()->where('owner_association_id',auth()->user()->owner_association_id)->whereNotIn('id', $existingServices)->pluck('name', 'id')->toArray();
                //         return Select::make('recordId')
                //             ->label('Buildings')
                //             ->options($notSelected)
                //             ->searchable()
                //             ->required()
                //             ->preload();
                //     }),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                //Tables\Actions\DetachAction::make()->label('Remove'),
                // Tables\Actions\DeleteAction::make(),
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
