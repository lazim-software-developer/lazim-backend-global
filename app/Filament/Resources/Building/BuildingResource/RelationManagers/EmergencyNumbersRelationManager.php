<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use App\Models\EmergencyNumber;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EmergencyNumbersRelationManager extends RelationManager
{
    protected static string $relationship = 'emergencyNumbers';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->rules([function () {
                        return function (string $attribute, $value, Closure $fail) {
                            if (!preg_match('/^[a-zA-Z]+(?:\s+[a-zA-Z]+)*$/', $value)) {
                                $fail('The Name format is invalid. It must contain only alphabetic characters and spaces.');
                            }
                        };
                    }])
                    ->required()
                    ->maxLength(50),
                TextInput::make('number')
                    ->rules(['regex:/^\d{1,10}$/',function (?Model $record) {
                        return function (string $attribute, $value, Closure $fail) use ($record){
                            $exists = EmergencyNumber::where('building_id',$this->ownerRecord->id)->where('number',$value)->exists();
                            if($record === null && $exists){
                                $fail('The Entered phone number already Exists!');
                            }
                            if($record != null && EmergencyNumber::whereNot('id',$record->id)->where('building_id',$this->ownerRecord->id)->where('number',$value)->exists()) {
                                $fail('The Entered phone number already Exists!');
                            }
                        };

                    }])
                    ->prefix('+971')
                    ->numeric()
                    ->required()
                    ->label('Phone Number')
                    ->maxLength(15),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('number')->label('Phone Number')->prefix('+971')->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
