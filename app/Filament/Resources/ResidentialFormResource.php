<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResidentialFormResource\Pages;
use App\Filament\Resources\ResidentialFormResource\RelationManagers;
use App\Models\ResidentialForm;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
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
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([
                    TextInput::make('unit_occupied_by')
                        ->label('Unit Occupied By'),
                    TextInput::make('passport_number')
                        ->label('Passport Number')
                        ->placeholder('Passport Number'),
                    TextInput::make('number_of_children')
                        ->label('Number Of Children')
                        ->placeholder('Number Of Children'),
                    TextInput::make('number_of_adults')
                        ->label('Number Of Adults'),
                    Select::make('building_id')
                        ->relationship('building','name')
                        ->preload()
                        ->searchable()
                        ->label('Building Name'),
                    Select::make('flat_id')
                        ->relationship('flat','property_number')
                        ->preload()
                        ->searchable()
                        ->label('Property No'),
                    Select::make('user_id')
                        ->rules(['exists:users,id'])
                        ->relationship('user', 'first_name')
                        ->required()
                        ->preload()
                        ->searchable()
                        ->label('User'),
                    TextInput::make('office_number')
                        ->label('Office Number'),
                    TextInput::make('trn_number')
                        ->label('TRN Number'),
                    TextInput::make('unit_occupied_by')
                        ->label('Unit Occupied By'),
                    TextInput::make('emirates_id')
                        ->label('Emirates Id'),
                    TextInput::make('title_deed_number')
                        ->label('Title Deed Number'),
                    TextInput::make('status')
                        ->required()
                        ->label('Status'),
                    TextInput::make('remarks')
                        ->required()
                        ->label('Remarks'),
                    FileUpload::make('title_deed_url')
                        ->disk('s3')
                        ->directory('dev')
                        ->label('Title Deed Url')
                        ->downloadable()
                        ->openable()
                        ->columnSpan([
                            'sl'=>1,
                            'md'=>1,
                            'lg'=>2,
                        ]),
                    FileUpload::make('emirates_url')
                        ->disk('s3')
                        ->directory('dev')
                        ->label('Emirates Url')
                        ->downloadable()
                        ->openable()
                        ->columnSpan([
                            'sl'=>1,
                            'md'=>1,
                            'lg'=>2,
                        ]),
                    FileUpload::make('passport_url')
                        ->disk('s3')
                        ->directory('dev')
                        ->label('Passport Url')
                        ->downloadable()
                        ->openable()
                        ->columnSpan([
                            'sl'=>1,
                            'md'=>1,
                            'lg'=>2,
                        ]),

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                ImageColumn::make('passport_url')
                    ->square()
                    ->disk('s3'),
                ImageColumn::make('emirates_url')
                    ->square()
                    ->disk('s3'),
                ImageColumn::make('title_deed_url')
                    ->square()
                    ->disk('s3'),
                TextColumn::make('status')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('remarks')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
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
                    ->visible(fn ($record) => $record->status === null)
                    ->button()
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
            'view' => Pages\ViewResidentialForm::route('/{record}'),
        ];
    }    
}
