<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\AppFeedback;
use App\Models\GlobalSetting;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EmailReminderResource\Pages;
use App\Filament\Resources\EmailReminderResource\RelationManagers;

class EmailReminderResource extends Resource
{
    protected static ?string $model = GlobalSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('payment_day')
                ->label('Payment Day')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('follow_up_day')
                    ->label('Follow Up Day')
                    ->required()
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_day')
                    ->searchable()
                    ->label('Payment Day'),
                Tables\Columns\TextColumn::make('follow_up_day')->searchable()->label('Follow Up Day'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
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
            'index'  => Pages\ListEmailReminder::route('/'),
            'edit' => Pages\EditEmailReminder::route('/{record}/edit'),
        ];
    }
}
