<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Toggle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use App\Filament\Resources\OwnerAssociationResource\Pages;

class OwnerAssociationResource extends Resource
{
    protected static ?string $model                 = OwnerAssociation::class;
    protected static ?string $modelLabel            = 'Owner Association';
    protected static ?string $navigationIcon        = 'heroicon-o-rectangle-stack';
    protected static bool $shouldRegisterNavigation = true;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([
                    TextInput::make('name')
                        ->rules(['regex:/^[a-zA-Z\s]*$/'])
                        ->required()
                        ->disabled(function (callable $get) {
                            return DB::table('owner_associations')
                                ->where('email', $get('email'))
                                ->where('verified', 1)
                                ->exists();
                        })
                        ->placeholder('User'),
                    TextInput::make('mollak_id')->label('Oa Number')
                        ->required()
                        ->disabled()

                        ->placeholder('OA Number'),
                    TextInput::make('trn_number')->label('TRN Number')
                        ->required()
                        ->disabled()
                        ->placeholder('TRN Number'),
                    TextInput::make('phone')
                        ->rules(['regex:/^(\971)(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/',function (Model $record) {
                            return function (string $attribute, $value, Closure $fail) use($record) {
                                if (DB::table('owner_associations')->whereNot('id',$record->id)->where('phone', $value)->count() > 0) {
                                    $fail('The phone is already taken by a OA.');
                                }
                                if(DB::table('owner_associations')->where('id',$record->id)->where('verified',1)->count() > 0){
                                    $getuserecord = User::where('owner_association_id',$record->id)->where('role_id',10)->first()->id;
                                    if (DB::table('users')->whereNot('id',$getuserecord)->where('phone', $value)->exists()) {
                                        $fail('The phone is already taken by a user.');
                                    }
                                }
                                else{
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
                            if(Role::where('id',auth()->user()->role_id)->first()->name == 'Admin')
                            {
                                return DB::table('owner_associations')
                                ->where('email', $get('email'))
                                ->where('verified', 1)
                                ->exists();
                            }
                            
                        })
                        ->placeholder('Address'),
                    TextInput::make('email')
                        ->rules(['min:6', 'max:30', 'regex:/^[a-z0-9.]+@[a-z]+\.[a-z]{2,}$/', function (Model $record) {
                            return function (string $attribute, $value, Closure $fail) use($record) {
                                if (DB::table('owner_associations')->whereNot('id',$record->id)->where('email', $value)->count() > 0) {
                                    $fail('The email is already taken by a OA.');
                                }
                                if(DB::table('owner_associations')->where('id',$record->id)->where('verified',1)->count() > 0){
                                    $getuserecord = User::where('owner_association_id',$record->id)->where('role_id',10)->first()->id;
                                    if (DB::table('users')->whereNot('id',$getuserecord)->where('email', $value)->exists()) {
                                        $fail('The email is already taken by a USER.');
                                    }
                                }
                                else{
                                    if (DB::table('users')->where('email', $value)->exists()) {
                                        $fail('The email is already taken by a USER.');
                                    }
                                }
                            };
                        },])
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
                            if(Role::where('id',auth()->user()->role_id)->first()->name == 'Admin')
                            {
                                return DB::table('owner_associations')
                                ->where('email', $get('email'))
                                ->where('verified', 1)
                                ->exists();
                            }
                            
                        })
                        ->placeholder('account number'),
                    FileUpload::make('profile_photo')
                        ->disk('s3')
                        ->directory('dev')
                        ->image()
                        ->maxSize(2048)
                        ->rules('file|mimes:jpeg,jpg,png|max:2048')
                        ->label('Profile Photo')
                        ->disabled(function (callable $get) {
                            if(Role::where('id',auth()->user()->role_id)->first()->name == 'Admin')
                            {
                                return DB::table('owner_associations')
                                ->where('email', $get('email'))
                                ->where('verified', 1)
                                ->exists();
                            }
                            
                        })
                        ->columnSpan([
                            'sm' => 1,
                            'md' => 2,
                            'lg' => 2,
                        ]),
                    FileUpload::make('trn_certificate')
                        ->disk('s3')
                        ->directory('dev')
                        ->maxSize(2048)
                        ->label('TRN Certificate')
                        ->disabled(function (callable $get) {
                            if(Role::where('id',auth()->user()->role_id)->first()->name == 'Admin')
                            {
                                return DB::table('owner_associations')
                                ->where('email', $get('email'))
                                ->where('verified', 1)
                                ->exists();
                            }
                            
                        }),
                    FileUpload::make('trade_license')
                        ->disk('s3')
                        ->directory('dev')
                        ->maxSize(2048)
                        ->label('Trade License')
                        ->disabled(function (callable $get) {
                            if(Role::where('id',auth()->user()->role_id)->first()->name == 'Admin')
                            {
                                return DB::table('owner_associations')
                                ->where('email', $get('email'))
                                ->where('verified', 1)
                                ->exists();
                            }
                            
                        }),
                    FileUpload::make('dubai_chamber_document')
                        ->disk('s3')
                        ->directory('dev')
                        ->maxSize(2048)
                        ->label('Dubai Chamber Document')
                        ->disabled(function (callable $get) {
                            if(Role::where('id',auth()->user()->role_id)->first()->name == 'Admin')
                            {
                                return DB::table('owner_associations')
                                ->where('email', $get('email'))
                                ->where('verified', 1)
                                ->exists();
                            }
                            
                        }),
                    FileUpload::make('memorandum_of_association')
                        ->disk('s3')
                        ->directory('dev')
                        ->maxSize(2048)
                        ->label('Memorandum of Association')
                        ->disabled(function (callable $get) {
                            if(Role::where('id',auth()->user()->role_id)->first()->name == 'Admin')
                            {
                                return DB::table('owner_associations')
                                ->where('email', $get('email'))
                                ->where('verified', 1)
                                ->exists();
                            }
                            
                        }),
                    Toggle::make('verified')
                        ->rules(['boolean'])
                        ->hidden(function ($record) {
                            return $record->verified;
                        }),
                    Toggle::make('active')
                        ->label('Active')
                        ->rules(['boolean'])
                        ->hidden(Role::where('id',auth()->user()->role_id)->first()->name != 'Admin'),

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('mollak_id')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('name')
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
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index'  => Pages\ListOwnerAssociations::route('/'),
            'create' => Pages\CreateOwnerAssociation::route('/create'),
            'edit'   => Pages\EditOwnerAssociation::route('/{record}/edit'),
        ];
    }
}
