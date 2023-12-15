<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
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
                        ->rules(['regex:/^(\+971)(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/',function () {
                            return function (string $attribute, $value, Closure $fail) {
                                if (DB::table('owner_associations')->where('phone', $value)->count() > 1) {
                                    $fail('The phone is already taken as OA.');
                                }
                                if (DB::table('users')->where('phone', $value)->exists()) {
                                    $fail('The phone is already taken as user.');
                                }
                            };
                        },
                        ])
                        ->required()
                        ->unique(
                            'users',
                            'phone',
                        )
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
                            return DB::table('owner_associations')
                                ->where('email', $get('email'))
                                ->where('verified', 1)
                                ->exists();
                        })
                        ->placeholder('Address'),
                    TextInput::make('email')
                        ->rules(['min:6', 'max:30', 'regex:/^[a-z0-9.]+@[a-z]+\.[a-z]{2,}$/', function () {
                            return function (string $attribute, $value, Closure $fail) {
                                if (DB::table('owner_associations')->where('email', $value)->count() > 1) {
                                    $fail('The email is already taken by a OA.');
                                }
                                if (DB::table('users')->where('email', $value)->exists()) {
                                    $fail('The email is already taken by a USER.');
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
                        ->unique(
                            'users',
                            'email',
                            modifyRuleUsing: function (Unique $rule, callable $get, ?Model $record) {
                                if (DB::table('users')->where('owner_association_id', $record->id)->exists()) {
                                    return $rule->whereNot('email', $get('email'));
                                }
                                return $rule->where('email', $get('email'));
                            }
                        )
                        ->placeholder('Email'),
                    FileUpload::make('profile_photo')
                        ->disk('s3')
                        ->directory('dev')
                        ->image()
                        ->maxSize(2048)
                        ->label('Profile Photo')
                        ->disabled(function (callable $get) {
                            return DB::table('owner_associations')
                                ->where('phone', $get('phone'))
                                ->where('verified', 1)
                                ->exists();
                        })
                        ->columnSpan([
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 2,
                        ]),
                    Toggle::make('verified')
                        ->rules(['boolean'])
                        ->hidden(function ($record) {
                            return $record->verified;
                        }),
                    Toggle::make('active')
                        ->label('Active')
                        ->rules(['boolean']),

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
                    Tables\Actions\DeleteBulkAction::make(),
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
