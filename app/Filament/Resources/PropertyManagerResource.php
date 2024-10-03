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
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

//
class PropertyManagerResource extends Resource
{
    protected static ?string $model = OwnerAssociation::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $modelLabel = 'Property Manager';

    protected static bool $shouldRegisterNavigation = true;

    protected static bool $isScopedToTenant = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([

                    // Name Field
                    TextInput::make('name')
                        ->label('Company Name')
                        ->rules(['regex:/^[a-zA-Z0-9\s]*$/'])
                        ->required()
                        ->disabledOn('edit')
                        ->placeholder('User'),

                    TextInput::make('trn_number')
                        ->label('TRN Number')
                        ->disabledOn('edit')
                        ->required()
                        ->placeholder('TRN Number'),

                    TextInput::make('trade_license_number')
                        ->label('Trade License Number')
                        ->required()
                        ->disabledOn('edit')
                        ->placeholder('Trade License Number'),
                    // Phone Field with Unique Validation
                    TextInput::make('phone')
                        ->required()
                        ->disabledOn('edit')
                        ->rules([
                            'regex:/^\+?(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/',
                        ])
                        ->placeholder('Contact Number')
                        ->unique('owner_associations', 'phone', fn(?Model $record) => $record),

                    TextInput::make('email')
                        ->required()
                        ->disabledOn('edit')
                        ->rules([
                            'required',
                            'email',
                            'min:6',
                            'max:30',
                            'regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                        ])
                        ->placeholder('Email')
                        ->unique('owner_associations', 'email', fn(?Model $record) => $record),

                    // Additional Fields
                    TextInput::make('address')
                        ->required()
                        ->placeholder('Address'),

                    TextInput::make('bank_account_number')
                        ->label('Bank Account Number')
                        ->numeric()
                        ->placeholder('Account Number'),

                    FileUpload::make('profile_photo')
                        ->disk('s3')
                        ->directory('dev')
                        ->previewable(true)
                        ->image()
                        ->maxSize(2048)
                        ->rules('file|mimes:jpeg,jpg,png|max:2048')
                        ->label('Logo')
                        ->columnSpan([
                            'sm' => 1,
                            'md' => 2,
                            'lg' => 2,
                        ]),

                    // Other File Uploads
                    FileUpload::make('trn_certificate')
                        ->disk('s3')
                        ->directory('dev')
                        ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                        ->maxSize(2048)
                        ->label('TRN Certificate'),

                    FileUpload::make('trade_license')
                        ->disk('s3')
                        ->directory('dev')
                        ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                        ->required()
                        ->disabledOn('edit')
                        ->maxSize(2048)
                        ->label('Trade License'),

                    FileUpload::make('dubai_chamber_document')
                        ->disk('s3')
                        ->directory('dev')
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

                    Toggle::make('verified')
                        ->hidden()
                        ->rules(['boolean']),

                    Toggle::make('active')
                        ->label('Active')
                        ->rules(['boolean'])
                        ->default(true)
                        ->visibleOn('edit')
                        ->afterStateUpdated(function (bool $state, $record) {
                            if ($state === false) {
                                SendInactiveStatusJob::dispatch($record);
                            }
                        })
                        ->hidden(Role::where('id', auth()->user()->role_id)->first()->name != 'Admin'),

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            // ->modifyQueryUsing(function (Builder $query) {
            //     $user = User::where('id', $this->data->)
            // })
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
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('address')
                    ->default('NA')
                    ->limit(50)
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        // if (auth()->user()?->role?->name === 'Admin') {
            return [
                BuildingRelationManager::class,
                FlatsRelationManager::class,
            ];
        // }
        // return [];
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
