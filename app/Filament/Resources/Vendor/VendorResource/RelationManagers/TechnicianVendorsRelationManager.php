<?php

namespace App\Filament\Resources\Vendor\VendorResource\RelationManagers;

use App\Models\Building\Complaint;
use App\Models\User\User;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                TextInput::make('name')
                ->disabled()
                ->formatStateUsing(function($record){
                    return User::where('id',$record->technician_id)->value('first_name');
                }),
                TextInput::make('phone')
                ->disabled()
                ->formatStateUsing(function($record){
                    return User::where('id',$record->technician_id)->value('phone');
                }),
                Forms\Components\Toggle::make('active')
                ->rules([function ($record) {
                    return function (string $attribute, $value, Closure $fail) use ($record) {
                        if (!$value) {
                            if (!$value && $record) {
                                // Example: Check if there are open complaints for the technician
                                if (Complaint::where('technician_id', $record->technician_id)
                                    ->where('status', 'open')
                                    ->exists()) {

                                    // Prevent deactivation and send a custom validation error
                                    $fail('This technician has open complaints and cannot be deactivated.');
                                }
                            }
                        }
                    };
                },])
                
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
            ->defaultSort('created_at', 'desc')
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
                Tables\Actions\EditAction::make(),
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
