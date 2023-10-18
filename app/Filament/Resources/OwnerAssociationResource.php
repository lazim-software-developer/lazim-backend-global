<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OwnerAssociationResource\Pages;
use App\Models\OwnerAssociation;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 2,
                    'lg' => 2,
                ])->schema([
                    TextInput::make('name')
                        ->rules(['max:30','regex:/^[a-zA-Z\s]*$/'])
                        ->required()
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
                        ->rules(['regex:/^(\+971)(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/'])
                        ->required()
                        ->unique(
                            'users',
                            'phone',
                        )
                        ->placeholder('Contact Number'),
                    TextInput::make('address')
                        ->required()
                        ->placeholder('Address'),
                    TextInput::make('email')
                        ->rules(['min:6', 'max:30', 'regex:/^[a-z0-9.]+@[a-z]+\.[a-z]{2,}$/'])
                        ->required()
                        ->disabled(function (callable $get) {
                            return DB::table('owner_associations')
                                ->where('phone',$get('phone'))
                                ->where('verified', 1)
                                ->exists();
                        })
                        ->unique(
                            'users',
                            'email',
                            modifyRuleUsing: function (Unique $rule,callable $get,?Model $record) {
                                if(DB::table('users')->where('owner_association_id',$record->id)->exists())
                                {
                                    return $rule->whereNot('email',$get('email'));
                                }
                                return $rule->where('email',$get('email'));
                            }
                        )
                        ->placeholder('Email'),
                    Toggle::make('verified')
                        ->rules(['boolean'])
                        ->disabled(function (callable $get) {
                            return DB::table('owner_associations')
                                ->where('phone',$get('phone'))
                                ->where('verified', 1)
                                ->exists();
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
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('name')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('phone')
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('email')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('trn_number')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('address')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('address')
                    ->toggleable(),
            ])
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
