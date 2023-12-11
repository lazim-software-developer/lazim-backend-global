<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\NocForm;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Forms\SaleNOC;
use App\Models\Forms\NocForms;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\NocFormResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\NocFormResource\RelationManagers;

class NocFormResource extends Resource
{
    protected static ?string $model = SaleNOC::class;
    protected static ?string $modelLabel = 'Sale NOC';
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
                            TextInput::make('unit_occupied_by')->disabled(),
                            TextInput::make('applicant')->disabled(),
                            TextInput::make('unit_area')->disabled(),
                            TextInput::make('sale_price')->disabled()->prefix('AED'),
                            TextInput::make('signing_authority_email')->disabled(),
                            TextInput::make('signing_authority_phone')->disabled(),
                            TextInput::make('signing_authority_name')->disabled(),
                            Select::make('user_id')
                                ->rules(['exists:users,id'])
                                ->relationship('user', 'first_name')
                                ->preload()
                                ->disabled()
                                ->searchable()
                                ->label('User'),
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
                            DatePicker::make('service_charge_paid_till')
                                ->disabled()
                                ->date(),
                            Repeater::make('contacts')
                                ->disabled()
                                ->relationship()
                                ->schema([
                                    TextInput::make('type'),
                                    TextInput::make('first_name'),
                                    TextInput::make('last_name'),
                                    TextInput::make('email'),
                                    TextInput::make('mobile'),
                                    TextInput::make('emirates_id'),
                                    TextInput::make('passport_number'),
                                    TextInput::make('visa_number'),
                                    FileUpload::make('emirates_document_url')
                                        ->disk('s3')
                                        ->directory('dev')
                                        ->label('Emirates Document Url')
                                        ->downloadable(true)
                                        ->openable(true),
                                    FileUpload::make('visa_document_url')
                                        ->disk('s3')
                                        ->directory('dev')
                                        ->label('Visa Document Url')
                                        ->downloadable(true)
                                        ->openable(true),
                                    FileUpload::make('passport_document_url')
                                        ->disk('s3')
                                        ->directory('dev')
                                        ->label('Passport Document Url')
                                        ->downloadable(true)
                                        ->openable(true),
                                ])
                                ->columnSpan([
                                    'sm' => 1,
                                    'md' => 1,
                                    'lg' => 2,
                                ]),
                            FileUpload::make('cooling_receipt')
                                ->disk('s3')
                                ->disabled()
                                ->directory('dev')
                                ->label('Cooling Receipt')
                                ->downloadable(true)
                                ->openable(true)
                                ->columnSpan([
                                    'sm' => '1',
                                    'md' => '1',
                                    'lg' => '2',
                                ]),
                            FileUpload::make('cooling_soa')
                                ->disk('s3')
                                ->disabled()
                                ->directory('dev')
                                ->label('Cooling Soa')
                                ->downloadable(true)
                                ->openable(true)
                                ->columnSpan([
                                    'sm' => '1',
                                    'md' => '1',
                                    'lg' => '2',
                                ]),
                            FileUpload::make('cooling_clearance')
                                ->disk('s3')
                                ->disabled()
                                ->directory('dev')
                                ->label('Cooling Clearance')
                                ->downloadable(true)
                                ->openable(true)
                                ->columnSpan([
                                    'sm' => '1',
                                    'md' => '1',
                                    'lg' => '2',
                                ]),
                            FileUpload::make('payment_receipt')
                                ->disk('s3')
                                ->disabled()
                                ->directory('dev')
                                ->label('Payment Receipt')
                                ->downloadable(true)
                                ->openable(true)
                                ->columnSpan([
                                    'sm' => '1',
                                    'md' => '1',
                                    'lg' => '2',
                                ]),
                            Toggle::make('cooling_bill_paid')
                                ->disabled()
                                ->columnSpan([
                                    'sm' => '1',
                                    'md' => '1',
                                    'lg' => '2',
                                ]),
                            Toggle::make('service_charge_paid')
                                ->disabled()
                                ->columnSpan([
                                    'sm' => '1',
                                    'md' => '1',
                                    'lg' => '2',
                                ]),
                            Toggle::make('noc_fee_paid')
                                ->disabled()
                                ->columnSpan([
                                    'sm' => '1',
                                    'md' => '1',
                                    'lg' => '2',
                                ]),
                            Select::make('status')
                                ->options([
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                ])
                                ->disabled(function(SaleNOC $record){
                                    return $record->status != null;
                                })
                                ->required()
                                ->searchable()
                                ->live(),
                            TextInput::make('remarks')
                                ->rules(['max:255'])
                                ->visible(function (callable $get) {
                                    if ($get('status') == 'rejected') {
                                        return true;
                                    }
                                    return false;
                                })
                                ->disabled(function(SaleNOC $record){
                                    return $record->status != null;
                                })
                                ->required(),
                        ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.first_name')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('status')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('remarks')
                    ->searchable()
                    ->default('NA'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
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
            'index' => Pages\ListNocForms::route('/'),
            // 'view' => Pages\ViewNocForm::route('/{record}'),
            'edit' => Pages\EditNocForm::route('/{record}/edit'),
        ];
    }
}
