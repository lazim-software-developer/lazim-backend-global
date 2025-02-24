<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppFeedbackResource\Pages;
use App\Filament\Resources\AppFeedbackResource\RelationManagers;
use App\Models\AppFeedback;
use App\Models\User\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppFeedbackResource extends Resource
{
    protected static ?string $model = AppFeedback::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                ->label('User')
                ->formatStateUsing(function($state){
                    return  User::where('id',$state)->value('first_name');
                 })
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('subject')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(191),
                Forms\Components\Textarea::make('comment')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')->label('User')
                ->formatStateUsing(function($state){
                   return  User::where('id',$state)->value('first_name');
                }),
                Tables\Columns\TextColumn::make('subject')->limit(50),
                Tables\Columns\TextColumn::make('comment')->limit(50)
                    
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListAppFeedback::route('/'),
            'create' => Pages\CreateAppFeedback::route('/create'),
            // 'edit' => Pages\EditAppFeedback::route('/{record}/edit'),
            'view' => Pages\ViewAppFeedback::route('/{record}'),
        ];
    }
}
