<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlatsRelationManagerResource\RelationManagers\FlatsRelationManager;
use App\Filament\Resources\PropertyManagerResource\Pages;
use App\Filament\Resources\PropertyManagerResource\RelationManagers\BuildingRelationManager;
use App\Jobs\SendInactiveStatusJob;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PropertyManagerResource extends Resource
{
    protected static ?string $model                 = OwnerAssociation::class;
    protected static ?string $navigationIcon        = 'heroicon-o-building-office';
    protected static ?string $modelLabel            = 'Property Manager';
    protected static bool $shouldRegisterNavigation = true;
    protected static bool $isScopedToTenant         = false;
    protected static ?string $navigationGroup       = 'Property Management';
    protected static ?int $navigationSort           = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Company Information')
                    ->description('Enter the basic details of the property management company.')
                    ->icon('heroicon-o-building-library')
                    ->columns(3)
                    ->collapsible()
                    ->schema([
                        TextInput::make('name')
                            ->label('Company Name')
                            ->rules(['regex:/^[a-zA-Z0-9\s]*$/'])
                            ->required()
                            ->disabledOn('edit')
                            ->placeholder('Enter company name'),

                        TextInput::make('trn_number')
                            ->label('TRN Number')
                            ->disabledOn('edit')
                            ->required()
                            ->placeholder('Enter TRN number'),

                        TextInput::make('trade_license_number')
                            ->label('Trade License Number')
                            ->required()
                            ->disabledOn('edit')
                            ->unique('owner_associations', 'trade_license_number', fn(?Model $record) => $record)
                            ->placeholder('Enter trade license number'),
                    ]),

                Section::make('Contact Details')
                    ->description('Provide contact information for the company.')
                    ->icon('heroicon-o-phone')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextInput::make('phone')
                            ->required()
                            ->disabledOn('edit')
                            ->tel()
                            ->placeholder('5XXXXXXXX')
                            ->rules([
                                'regex:/^\+?(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/',
                            ])
                            ->prefix('+971')
                            ->unique('owner_associations', 'phone', fn(?Model $record) => $record),

                        TextInput::make('email')
                            ->required()
                            ->disabledOn('edit')
                            ->email()
                            ->rules([
                                'required',
                                'email',
                                'min:6',
                                'max:30',
                                'regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                            ])
                            ->placeholder('company@example.com')
                            ->unique('owner_associations', 'email', fn(?Model $record) => $record),

                        TextInput::make('address')
                            ->required()
                            ->placeholder('Enter complete address')
                            ->columnSpanFull(),
                    ]),

                Section::make('Financial Information')
                    ->description('Enter banking details for the company.')
                    ->icon('heroicon-o-banknotes')
                    ->collapsible()
                    ->schema([
                        TextInput::make('bank_account_number')
                            ->label('Bank Account Number')
                            ->numeric()
                            ->reactive()
                            ->disabled(function (?Model $record) {
                                return $record && $record->bank_account_number;
                            })
                            ->placeholder('Enter account number'),
                        TextInput::make('bank_account_holder_name')
                            ->label('Bank Account holder name')
                            ->reactive()
                            ->visible(function (callable $get) {
                                return $get('bank_account_number');
                            })
                            ->required(function (callable $get) {
                                return $get('bank_account_number');
                            })
                            ->placeholder('Enter bank account holder name'),
                    ])->columns(2),

                Section::make('Documents')
                    ->description('Upload required company documents.')
                    ->icon('heroicon-o-document')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        FileUpload::make('profile_photo')
                            ->disk('s3')
                            ->directory('dev')
                            ->image()
                            ->openable(true)
                            ->downloadable(true)
                            ->maxSize(2048)
                            ->rules('file|mimes:jpeg,jpg,png|max:2048')
                            ->label('Company Logo')
                            ->columnSpanFull(),

                        FileUpload::make('trn_certificate')
                            ->disk('s3')
                            ->directory('dev')
                            ->openable(true)
                            ->downloadable(true)
                            ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                            ->maxSize(2048)
                            ->label('TRN Certificate'),

                        FileUpload::make('trade_license')
                            ->disk('s3')
                            ->directory('dev')
                            ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                            ->openable(true)
                            ->downloadable(true)
                            ->required()
                            ->disabledOn('edit')
                            ->maxSize(2048)
                            ->label('Trade License'),

                        FileUpload::make('dubai_chamber_document')
                            ->disk('s3')
                            ->directory('dev')
                            ->openable(true)
                            ->downloadable(true)
                            ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                            ->maxSize(2048)
                            ->label('Other Document'),

                        FileUpload::make('memorandum_of_association')
                            ->disk('s3')
                            ->required()
                            ->directory('dev')
                            ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                            ->maxSize(2048)
                            ->label('Memorandum of Association'),
                    ]),

                Section::make('Status')
                    ->description('Manage the status of the property manager.')
                    ->icon('heroicon-o-check-circle')
                    ->collapsible()
                    ->schema([
                        Toggle::make('verified')
                            ->hidden()
                            ->rules(['boolean']),

                        Toggle::make('active')
                            ->label('Active Status')
                            ->rules(['boolean'])
                            ->default(true)
                            ->live()
                            ->visibleOn('edit')
                            ->onIcon('heroicon-o-check-circle')
                            ->offIcon('heroicon-o-x-mark')
                            ->onColor('success')
                            ->offColor('danger')
                            ->afterStateUpdated(function (bool $state, $record) {
                                if ($state === false) {
                                    SendInactiveStatusJob::dispatch($record);
                                }
                            }),

                    ])
                    ->hidden(Role::where('id', auth()->user()->role_id)
                            ->first()->name != 'Admin')
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Property Name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),

                Tables\Columns\TextColumn::make('trn_number')
                    ->label('TRN Number')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),

                Tables\Columns\TextColumn::make('address')
                    ->default('NA')
                    ->limit(50)
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ])
            ->emptyStateActions([]);
    }

    public static function getRelations(): array
    {
        return [
            BuildingRelationManager::class,
            FlatsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPropertyManagers::route('/'),
            'create' => Pages\CreatePropertyManager::route('/create'),
            'edit'   => Pages\EditPropertyManager::route('/{record}/edit'),
        ];
    }
}
