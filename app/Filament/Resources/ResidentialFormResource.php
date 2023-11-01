<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResidentialFormResource\Pages;
use App\Filament\Resources\ResidentialFormResource\RelationManagers;
use App\Models\ResidentialForm;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResidentialFormResource extends Resource
{
    protected static ?string $model = ResidentialForm::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('unit_occupied_by')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('name')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('passport_number')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('building.name')
                    ->searchable()
                    ->label('Building')
                    ->default('NA'),
                TextColumn::make('user.first_name')
                    ->searchable()
                    ->label('Resident Name')
                    ->default('NA'),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->label('Flat Number')
                    ->default('NA'),
                TextColumn::make('number_of_adults')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('number_of_children')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('office_number')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('trn_number')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('unit_occupied_by')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('passport_expires_on')
                    ->date()
                    ->searchable(),
                TextColumn::make('emirates_id')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('emirates_expires_on')
                    ->date()
                    ->searchable(),
                TextColumn::make('title_deed_number')
                    ->searchable()
                    ->default('NA'),
                // TextColumn::make('emergency_contact')
                //     ->searchable()
                //     ->default('NA'),
                ImageColumn::make('passport_url')
                    ->circular()
                    ->disk('s3'),
                ImageColumn::make('emirates_url')
                    ->circular()
                    ->disk('s3'),
                ImageColumn::make('title_deed_url')
                    ->circular()
                    ->disk('s3'),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->relationship('user', 'first_name')
                    ->searchable()
                    ->preload()
                    ->label('User'),
                SelectFilter::make('building_id')
                    ->relationship('building', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Building'),
                SelectFilter::make('flat_id')
                    ->relationship('flat', 'property_number')
                    ->searchable()
                    ->preload()
                    ->label('Flat Number'),
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
                Action::make('Update Status')
                    ->form([
                        Select::make('status')
                            ->options([
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->searchable()
                            ->live(),
                        TextInput::make('remarks')
                            ->rules(['max:255'])
                            ->visible(function(callable $get){
                                if($get('status')=='rejected')
                                {
                                    return true;
                                }
                                return false;
                            }),
                    ])
                    ->fillForm(fn (ResidentialForm $record): array => [
                        'status' => $record->status,
                        'remarks' => $record->remarks,
                    ])
                    ->action(function (ResidentialForm $record,array $data): void {
                        if($data['status'] == 'rejected')
                        {
                            $record->status = $data['status'];
                            $record->remarks = $data['remarks'];
                            $record->save();
                        }
                        else
                        {
                            $record->status = $data['status'];
                            $record->save();
                        }
                    })
                    ->slideOver()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                //Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListResidentialForms::route('/'),
            //'create' => Pages\CreateResidentialForm::route('/create'),
            //'edit' => Pages\EditResidentialForm::route('/{record}/edit'),
        ];
    }    
}
