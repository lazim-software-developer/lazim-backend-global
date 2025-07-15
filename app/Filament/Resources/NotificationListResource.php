<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Notification;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\NotificationListResource\Pages;


class NotificationListResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static ?string $modelLabel  = 'Notifications';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('read_at')
                    ->disabled()
                    ->label('Read At'),
                // Textarea::make('data.body')->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->default('NA')
                    ->label('Notification Title'),
                TextColumn::make('read_at')
                    ->searchable()
                    ->sortable()
                    ->default('NA')
                    ->label('Read At'),
                //
            ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(function (Notification $record) {
                        // dd($record->id,$record->data['actions'][0]['url']);
                        // Log::info('Notification record ID: ' . json_encode($record));
                        return $record?->data['actions'][0]['url'];
                    })
                    ->icon('heroicon-o-eye')
                    ->label('View Notification'),
                // Tables\Actions\EditAction::make(),


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
            'index' => Pages\ListNotificationSents::route('/'),
            'view' => Pages\ViewNotificationSent::route('/{record}'),
        ];
    }
}
