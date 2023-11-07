<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccessCardFormsDocumentResource\Pages;
use App\Models\Forms\AccessCard;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccessCardFormsDocumentResource extends Resource
{
    protected static ?string $model = AccessCard::class;

    protected static ?string $modelLabel = 'AccessCard';
    protected static ?string $navigationGroup = 'Forms Document';
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
                    TextInput::make('card_type')
                        ->required()
                        ->label('Card Type'),
                    TextInput::make('email')
                        ->label('Email')
                        ->required()
                        ->placeholder('Email'),
                    TextInput::make('mobile')
                        ->label('Mobile Number')
                        ->required()
                        ->placeholder('Mobile Number'),
                    TextInput::make('parking_details')
                        ->required()
                        ->placeholder('Parking Details'),
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
                    TextInput::make('status')
                        ->required()
                        ->label('Status'),
                    TextInput::make('remarks')
                        ->required()
                        ->label('Remarks'),
                    // FileUpload::make('passport')
                    //     ->disk('s3')
                    //     ->directory('dev')
                    //     ->label('Passport'),
                    FileUpload::make('tenancy')
                        ->disk('s3')
                        ->directory('dev')
                        ->downloadable()
                        ->openable()
                        ->label('Tenancy')
                        ->columnSpan([
                            'sm'=> 1,
                            'md'=> 1,
                            'lg'=> 2,
                        ]),
                    FileUpload::make('vehicle_registration')
                        ->disk('s3')
                        ->directory('dev')
                        ->downloadable()
                        ->openable()
                        ->label('Vehicle Registration')
                        ->columnSpan([
                            'sm'=> 1,
                            'md'=> 1,
                            'lg'=> 2,
                        ]),

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('card_type')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('user.first_name')
                        ->searchable()
                        ->default('NA')
                        ->limit(50),
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                ImageColumn::make('tenancy')
                    ->label('Tenancy')
                    ->square()
                    ->alignCenter()
                    ->disk('s3'),
                ImageColumn::make('vehicle_registration')
                    ->label('Vehicle Registration')
                    ->square()
                    ->alignCenter()
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
                //
            ])
            ->actions([
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
                            ->visible(function (callable $get) {
                                if ($get('status') == 'rejected') {
                                    return true;
                                }
                                return false;
                            }),
                    ])
                    ->fillForm(fn (AccessCard $record): array => [
                        'status' => $record->status,
                        'remarks' => $record->remarks,
                    ])
                    ->action(function (AccessCard $record, array $data): void {
                        if ($data['status'] == 'rejected') {
                            $record->status = $data['status'];
                            $record->remarks = $data['remarks'];
                            $record->save();
                        } else {
                            $record->status = $data['status'];
                            $record->save();
                        }
                    })
                    ->slideOver()
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
            'index' => Pages\ListAccessCardFormsDocuments::route('/'),
            //'create' => Pages\CreateAccessCardFormsDocument::route('/create'),
            //'edit' => Pages\EditAccessCardFormsDocument::route('/{record}/edit'),
            'view' => Pages\ViewAccessCardFormsDocument::route('/{record}'),
        ];
    }
}
