<?php

namespace App\Filament\Resources\Vendor\VendorResource\RelationManagers;

use App\Models\User\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class TechnicianVendorsRelationManager extends RelationManager
{
    protected static string $relationship = 'technicianVendors';
    protected static ?string $inverseRelationship = 'vendor';
    public static function getTitle(Model $ownerRecord, string $pageClass): string    
    {         
        return 'Technician';     
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('active')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.first_name')->limit(50)->label('Name'),
                Tables\Columns\TextColumn::make('user.phone')->limit(50)->label('Phone'),
                Tables\Columns\IconColumn::make('active')->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
                // Tables\Actions\AssociateAction::make()
                // ->label('Add')
                //     ->recordSelect(function (RelationManager $livewire) {
                //         $vendorId = $livewire->ownerRecord->id;
                        
                //         // Get all the Users
                //         $allBuildings = User::all()->pluck('id')->toArray();
                //         $existingServices =  DB::table('technician_vendors')
                //             ->where('vendor_id', $vendorId)
                //             ->whereIn('technician_id', $allBuildings)->pluck('technician_id')->toArray();
                //         $notSelected = User::all()->where('role_id',13)->whereNotIn('id', $existingServices)->pluck('first_name', 'id')->toArray();
                //         return Select::make('recordId')
                //             ->label('Technician')
                //             ->options($notSelected)
                //             ->searchable()
                //             ->required()
                //             ->preload();
                //     }),
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
                // Tables\Actions\DissociateAction::make(),
                //Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DissociateBulkAction::make(),
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                //Tables\Actions\CreateAction::make(),
                //Tables\Actions\AssociateAction::make(),
            ]);
    }
}
