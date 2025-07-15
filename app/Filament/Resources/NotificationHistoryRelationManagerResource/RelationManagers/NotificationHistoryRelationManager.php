<?php

namespace App\Filament\Resources\NotificationHistoryRelationManagerResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\User\UserResource;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class NotificationHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'histories';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('action')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('History')
            ->columns([
                // Tables\Columns\TextColumn::make('user_id')
                //     ->label('User')
                //     ->formatStateUsing(fn($state): string => User::where('id', $state)->value('first_name') ?: 'NA')
                //     ->url(fn($record) => Filament::getUrlGenerator()->generateResourceUrl(
                //         resource: UserResource::class,
                //         name: 'edit',
                //         parameters: ['record' => $record->user_id]
                //     ))
                //     ->openUrlInNewTab() // Optional — remove if you want same tab
                //     ->color('primary') // Optional — blue link style
                //     // ->underline()     // Optional — underline text to look like link
                //     ->placeholder('NA')
                //     ->disabled(),

                TextColumn::make('user_id')
                    ->label('User')
                    ->formatStateUsing(fn($state) => User::find($state)?->first_name ?? 'NA')
                    ->url(fn($record) => UserResource::getUrl(name: 'edit', parameters: ['record' => $record->user_id]))
                    ->openUrlInNewTab()
                    ->color('primary'),
                // ->underline(),
                TextColumn::make('action'),
                TextColumn::make('read_at'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
