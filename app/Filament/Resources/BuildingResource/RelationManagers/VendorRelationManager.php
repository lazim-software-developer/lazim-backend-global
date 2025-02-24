<?php

namespace App\Filament\Resources\BuildingResource\RelationManagers;

use App\Models\BuildingVendor;
use App\Models\TechnicianVendor;
use App\Models\User\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class VendorRelationManager extends RelationManager
{
    protected static string $relationship = 'vendors';
    protected static ?string $title       = 'Technicians';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('id')
                //     ->required()
                //     ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        $vendorID = BuildingVendor::where('building_id',$this->ownerRecord->id)->distinct()->pluck('vendor_id')->toArray();
        $technicianID= TechnicianVendor::whereIn('vendor_id',$vendorID)->pluck('technician_id')->toArray();
        return $table
            ->query(User::whereIn('id',$technicianID))
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('first_name')->label('Name'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('technician_number')
                    ->label('Technician Number')
                    ->getStateUsing(function ($record) {
                    return DB::table('technician_vendors')
                        ->where('technician_id', $record->id)
                        ->value('technician_number');
                        }),
                    Tables\Columns\TextColumn::make('vendor_name')
                        ->label('Vendor Name')
                        ->getStateUsing(function ($record) {
                            $vendorID = DB::table('technician_vendors')
                                ->where('technician_id', $record->id)
                                ->value('vendor_id');
            
                            return DB::table('vendors')
                                ->where('id', $vendorID)
                                ->value('name');
                        }),
            
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
}
