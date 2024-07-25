<?php

namespace App\Filament\Resources\OwnerAssociationResource\RelationManagers;

use Closure;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use App\Models\AccountCredentials;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;

class AccountcredentialsRelationManager extends RelationManager
{
    protected static string $relationship = 'mailCredentials';

    protected static ?string $title = 'Mail Configuration';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('username')
                    ->required()
                    ->rules([
                        'regex:/^[a-zA-Z0-9]+$/',
                    ]),
                TextInput::make('email')
                    ->required()
                    ->minLength(6)
                    ->maxLength(30)
                    ->rules(['min:6', 'max:30', 'regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/']),
                TextInput::make('password')
                    ->required()
                    ->minLength(8),
                TextInput::make('mailer')
                    ->required()
                    ->string()
                    ->minLength(3)
                    ->maxLength(30),
                TextInput::make('host')
                    ->required()
                    ->string()
                    ->minLength(10)
                    ->maxLength(35)
                    ->rules([
                        'regex:/^[a-z.]+$/'
                    ]),
                TextInput::make('port')
                    ->required()
                    ->integer()
                    ->maxValue(9999),
                TextInput::make('encryption')
                    ->required()
                    ->string()
                    ->minLength(3)
                    ->maxLength(30),
                Toggle::make('active')
                ->rules(['boolean', function (?Model $record) {
                    return function (string $attribute, $value, Closure $fail) use ($record) {
                            if (AccountCredentials::where('oa_id', Filament::getTenant()->id)->where('active', true)->whereNotIn('id',[$record?->id])->exists() && $record != null && $value) {
                                $fail('A Active Security already exists for this building.');
                            }
                            if (AccountCredentials::where('oa_id', Filament::getTenant()->id)->where('active', true)->exists() && $record == null && $value) {
                                $fail('A Active Security already exists for this building.');
                            }
                    };
                }])
                ->reactive(),   
                
                Hidden::make('oa_id')
                    ->default(Filament::getTenant()?->id ?? auth()->user()?->owner_association_id),
                Hidden::make('created_by')
                    ->default(auth()->user()->id),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('username'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('host'),
                Tables\Columns\TextColumn::make('mailer'),
                Tables\Columns\IconColumn::make('active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                ->label('Create'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    // public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    // {
    //     return auth()->user()->role === 'OA';
    // }
}
