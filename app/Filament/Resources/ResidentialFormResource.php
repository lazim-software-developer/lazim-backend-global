<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ResidentialForm;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ResidentialFormResource\Pages;
use App\Filament\Resources\ResidentialFormResource\RelationManagers;
use Filament\Forms\Components\CheckboxList;

class ResidentialFormResource extends Resource {
    protected static ?string $model = ResidentialForm::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([
                            TextInput::make('unit_occupied_by')
                                ->disabled()
                                ->label('Unit Occupied By'),
                            TextInput::make('passport_number')
                                ->label('Passport Number')
                                ->disabled()
                                ->placeholder('Passport Number'),
                            TextInput::make('number_of_children')
                                ->label('Number Of Children')
                                ->disabled()
                                ->placeholder('Number Of Children'),
                            TextInput::make('number_of_adults')
                                ->label('Number Of Adults')->disabled(),
                            Select::make('building_id')
                                ->relationship('building', 'name')
                                ->preload()
                                ->disabled()
                                ->searchable()
                                ->label('Building Name'),
                            Select::make('flat_id')
                                ->relationship('flat', 'property_number')
                                ->preload()
                                ->disabled()
                                ->searchable()
                                ->label('Property No'),
                            Select::make('user_id')
                                ->rules(['exists:users,id'])
                                ->relationship('user', 'first_name')
                                ->required()
                                ->disabled()
                                ->preload()
                                ->searchable()
                                ->label('User'),
                            TextInput::make('office_number')
                                ->label('Office Number')->disabled(),
                            TextInput::make('trn_number')
                                ->label('TRN Number')->disabled(),
                            TextInput::make('emirates_id')
                                ->label('Emirates Id')->disabled(),
                            TextInput::make('title_deed_number')
                                ->label('Title Deed Number')->disabled(),
                            Textarea::make('emergency_contact')
                                ->label('Emergency Contact')
                                ->rows(10)
                                ->disabled(),
                            TextInput::make('passport_expires_on')
                                ->label('Passport Expires On')->disabled(),
                            TextInput::make('emirates_expires_on')
                                ->label('Emirates Expires On')->disabled(),
                            FileUpload::make('title_deed_url')
                                ->disk('s3')
                                ->directory('dev')
                                ->label('Title Deed Url')
                                ->disabled()
                                ->downloadable(true)
                                ->openable(true),
                            FileUpload::make('emirates_url')
                                ->disk('s3')
                                ->directory('dev')
                                ->disabled()
                                ->label('Emirates Url')
                                ->downloadable(true)
                                ->openable(true),
                            FileUpload::make('passport_url')
                                ->disk('s3')
                                ->directory('dev')
                                ->disabled()
                                ->label('Passport Url')
                                ->downloadable(true)
                                ->openable(true),
                            FileUpload::make('tenancy_contract')
                                ->disk('s3')
                                ->directory('dev')
                                ->disabled()
                                ->label('Tenancy / Ejari Url')
                                ->downloadable(true)
                                ->openable(true),
                            Select::make('status')
                                ->options([
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                ])
                                ->disabled(function (ResidentialForm $record) {
                                    return $record->status != null;
                                })
                                ->searchable()
                                ->live(),
                            TextInput::make('remarks')
                                ->rules(['max:255'])
                                ->visible(function (callable $get) {
                                    if($get('status') == 'rejected') {
                                        return true;
                                    }
                                    return false;
                                })
                                ->disabled(function (ResidentialForm $record) {
                                    return $record->status != null;
                                })
                                ->required(),
                            // If the form is rejected, we need to capture which fields are rejected
                            CheckboxList::make('rejected_fields')
                            ->label('Please select rejected fields')
                            ->options([
                                'passport_url' => 'Passport / EID',
                                'emirates_url' => 'Email',
                                'title_deed_url' => 'Mobile number',
                                'emirates_expires_on' => 'Emirates Expires Date',
                                'passport_expires_on' => 'Passport Expires Date',
                                'title_deed_number' => 'Title Deed Number',
                                'emirates_id' => 'Emirates Id',
                                'trn_number' => 'TRN Number',
                                'office_number' => 'Office Number',
                                'tenancy_contract' => 'Tenancy / Ejari',
                                'number_of_adults' => 'Number of adults',
                                'number_of_children' => 'Number Of Children',
                                'unit_occupied_by' => 'Unit Occupied By',
                            ])->columns(4)
                            ->columnSpan([
                                'sl' => 1,
                                'md' => 1,
                                'lg' => 2,
                            ])
                            ->visible(function (callable $get) {
                                if ($get('status') == 'rejected') {
                                    return true;
                                }
                                return false;
                            })

                        ]),
            ]);
    }

    public static function table(Table $table): Table {
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
            ->defaultSort('created_at', 'desc')
            ->actions([

            ]);
    }

    public static function getRelations(): array {
        return [
            //
        ];
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListResidentialForms::route('/'),
            // 'view' => Pages\ViewResidentialForm::route('/{record}'),
            'edit' => Pages\EditResidentialForm::route('/{record}/edit'),
        ];
    }
}
