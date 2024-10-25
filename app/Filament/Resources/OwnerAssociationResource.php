<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OwnerAssociationResource\Pages;
use App\Filament\Resources\OwnerAssociationResource\RelationManagers\AccountcredentialsRelationManager;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Unique;

class OwnerAssociationResource extends Resource
{
    protected static ?string $model                 = OwnerAssociation::class;
    protected static ?string $modelLabel            = 'Owner Association';
    protected static ?string $navigationIcon        = 'heroicon-o-rectangle-stack';
    protected static bool $shouldRegisterNavigation = true;

    protected static bool $isScopedToTenant = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('General Information')
                    ->schema([
                        Grid::make([
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 2,
                        ])->schema([
                            TextInput::make('name')
                                ->rules(['regex:/^[a-zA-Z0-9\s]*$/'])
                                ->required()
                                ->disabled(function (callable $get) {
                                    return DB::table('owner_associations')
                                        ->where('email', $get('email'))
                                        ->where('verified', 1)
                                        ->exists();
                                })
                                ->placeholder('User'),
                            TextInput::make('slug')
                                ->label('Slug')
                                ->required()
                                ->rules([
                                    'required',
                                    'string',
                                    'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                                    'min:4',
                                    'max:30',
                                ])
                                ->validationMessages([
                                    'regex' => 'Slug format is Invalid. It can only accept Lowercase letters, Numbers and hyphen',
                                ])
                                ->unique('owner_associations', 'slug', ignoreRecord: true)
                                ->disabled(function () {
                                    if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                        return false;
                                    } else {
                                        return true;
                                    }
                                }),
                            TextInput::make('mollak_id')->label('OA Number')
                                ->required()
                                ->disabled()
                                ->placeholder('OA Number'),
                            TextInput::make('trn_number')->label('TRN Number')
                                ->required()
                                ->disabled()
                                ->placeholder('TRN Number'),
                            TextInput::make('phone')
                                ->rules([
                                    'regex:/^\+?(971)(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/',
                                    function (Model $record) {
                                        return function (string $attribute, $value, Closure $fail) use ($record) {
                                            if (DB::table('owner_associations')->whereNot('id', $record->id)->where('phone', $value)->count() > 0) {
                                                $fail('The phone is already taken by an OA.');
                                            }
                                            if (DB::table('owner_associations')->where('id', $record->id)->where('verified', 1)->count() > 0) {
                                                $role_id      = Role::where('owner_association_id', $record->id)->where('name', 'OA')->first();
                                                $getuserecord = User::where('owner_association_id', $record->id)->where('role_id', $role_id?->id)->first()?->id;
                                                if (DB::table('users')->whereNot('id', $getuserecord)->where('phone', $value)->exists()) {
                                                    $fail('The phone is already taken by a user.');
                                                }
                                            } else {
                                                if (DB::table('users')->where('phone', $value)->exists()) {
                                                    $fail('The phone is already taken by a user.');
                                                }
                                            }
                                        };
                                    },
                                ])
                                ->required()
                                ->live()
                                ->disabled(function (callable $get) {
                                    return DB::table('owner_associations')
                                        ->where('email', $get('email'))
                                        ->where('verified', 1)
                                        ->exists();
                                })
                                ->placeholder('Contact Number'),
                            TextInput::make('address')
                                ->required()
                                ->disabled(function (callable $get) {
                                    if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                        return DB::table('owner_associations')
                                            ->where('email', $get('email'))
                                            ->where('verified', 1)
                                            ->exists();
                                    }
                                })
                                ->placeholder('Address'),
                            TextInput::make('email')
                                ->rules([
                                    'min:6', 'max:30', 'regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                                    function (Model $record) {
                                        return function (string $attribute, $value, Closure $fail) use ($record) {
                                            if (DB::table('owner_associations')->whereNot('id', $record->id)->where('email', $value)->count() > 0) {
                                                $fail('The email is already taken by an OA.');
                                            }
                                            if (DB::table('owner_associations')->where('id', $record->id)->where('verified', 1)->count() > 0) {
                                                $role_id      = Role::where('owner_association_id', $record->id)->where('name', 'OA')->first();
                                                $getuserecord = User::where('owner_association_id', $record->id)->where('role_id', $role_id?->id)->first()?->id;
                                                if (DB::table('users')->whereNot('id', $getuserecord)->where('email', $value)->exists()) {
                                                    $fail('The email is already taken by a USER.');
                                                }
                                            } else {
                                                if (DB::table('users')->where('email', $value)->exists()) {
                                                    $fail('The email is already taken by a USER.');
                                                }
                                            }
                                        };
                                    },
                                ])
                                ->required()
                                ->live()
                                ->disabled(function (callable $get) {
                                    return DB::table('owner_associations')
                                        ->where('phone', $get('phone'))
                                        ->where('verified', 1)
                                        ->exists();
                                })
                                ->placeholder('Email'),
                            TextInput::make('bank_account_number')
                                ->label('Bank Account Number')
                                ->numeric()
                                ->disabled(function (callable $get) {
                                    if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                        return DB::table('owner_associations')
                                            ->where('email', $get('email'))
                                            ->where('verified', 1)
                                            ->exists();
                                    }
                                })
                                ->placeholder('Account Number'),
                            Toggle::make('verified')
                                ->rules(['boolean'])
                                ->hidden(function ($record) {
                                    return $record->verified;
                                }),
                            Toggle::make('active')
                                ->label('Active')
                                ->rules(['boolean'])
                                ->hidden(Role::where('id', auth()->user()->role_id)->first()->name != 'Admin'),
                        ]),
                    ]),

                Section::make('Documents')
                    ->columns(3)
                    ->schema([
                        FileUpload::make('trn_certificate')
                            ->disk('s3')
                            ->directory('dev')
                            ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                            ->maxSize(2048)
                            ->label('TRN Certificate')
                            ->disabled(function (callable $get) {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return DB::table('owner_associations')
                                        ->where('email', $get('email'))
                                        ->where('verified', 1)
                                        ->exists();
                                }
                            }),
                        FileUpload::make('trade_license')
                            ->disk('s3')
                            ->directory('dev')
                            ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                            ->maxSize(2048)
                            ->label('Trade License')
                            ->disabled(function (callable $get) {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return DB::table('owner_associations')
                                        ->where('email', $get('email'))
                                        ->where('verified', 1)
                                        ->exists();
                                }
                            }),
                        FileUpload::make('dubai_chamber_document')
                            ->disk('s3')
                            ->directory('dev')
                            ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                            ->maxSize(2048)
                            ->label('Dubai Chamber Document')
                            ->disabled(function (callable $get) {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return DB::table('owner_associations')
                                        ->where('email', $get('email'))
                                        ->where('verified', 1)
                                        ->exists();
                                }
                            }),
                        FileUpload::make('memorandum_of_association')
                            ->disk('s3')
                            ->directory('dev')
                            ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                            ->maxSize(2048)
                            ->label('Memorandum of Association')
                            ->disabled(function (callable $get) {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return DB::table('owner_associations')
                                        ->where('email', $get('email'))
                                        ->where('verified', 1)
                                        ->exists();
                                }
                            }),
                        FileUpload::make('profile_photo')
                            ->disk('s3')
                            ->directory('dev')
                            ->previewable(true)
                            ->image()
                            ->maxSize(2048)
                            ->rules('file|mimes:jpeg,jpg,png|max:2048')
                            ->label('Logo')
                            ->disabled(function (callable $get) {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return DB::table('owner_associations')
                                        ->where('email', $get('email'))
                                        ->where('verified', 1)
                                        ->exists();
                                }
                            }),
                    ]),
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('mollak_id')
                    ->label('Mollak ID')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('slug')
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
        return [
            AccountcredentialsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOwnerAssociations::route('/'),
            'create' => Pages\CreateOwnerAssociation::route('/create'),
            'edit'   => Pages\EditOwnerAssociation::route('/{record}/edit'),
        ];
    }
}
