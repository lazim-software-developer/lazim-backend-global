<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Notification;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use App\Models\NotificationHistory;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Illuminate\Notifications\DatabaseNotification;
use App\Filament\Resources\NotificationListResource\Pages;
use App\Filament\Resources\NotificationHistoryRelationManagerResource\RelationManagers\NotificationHistoryRelationManager;

class NotificationListResource extends Resource
{
    protected static ?string $model = Notification::class;


    protected static ?string $modelLabel  = 'Notifications';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('data.building')
                    ->label('Building')
                    ->formatStateUsing(
                        fn($record) =>
                        Building::find(data_get($record->data, 'building'))?->name ?? 'NA'
                    )
                    ->disabled(),

                TextInput::make('user_first_name')
                    ->label('User')
                    ->formatStateUsing(fn($record) => optional($record->notifiable)?->first_name ?? 'NA')
                    ->disabled(),
                TextInput::make('data.title')
                    ->default('NA')
                    ->label('Notification Title'),
                TextInput::make('data.body')
                    ->label('Body'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('building')
                    ->label('Building')
                    ->state(
                        fn($record) =>
                        Building::find($record->building)?->name ?? 'NA'
                    ),
                TextColumn::make('user_first_name')
                    ->label('User')
                    ->sortable()
                    ->state(fn($record) => optional($record->notifiable)?->first_name ?? 'NA'),
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
            ])->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('data->building')
                    ->label('Filter by Building')
                    ->searchable()
                    ->options(
                        options: Building::where('owner_association_id', auth()->user()?->owner_association_id)
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['value'] ?? null),
                            fn(Builder $query) => $query->where('data->building', $data['value'])
                        );
                    }),
                SelectFilter::make('user_id')
                    ->label('Filter by User')
                    ->searchable()
                    ->options(
                        User::where('owner_association_id', auth()->user()?->owner_association_id)
                            ->pluck('first_name', 'id')
                            ->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['value'] ?? null),
                            fn(Builder $query) => $query
                                ->where('notifiable_type', User::class)
                                ->where('notifiable_id', $data['value'])
                        );
                    }),
                SelectFilter::make('notification_type_id')
                    ->label('Filter by Type')
                    ->searchable()
                    ->options(
                        DB::table('notification_types')
                            ->whereNotNull('name')
                            ->pluck('name', 'id') // 'id' is used to filter
                            ->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['value'] ?? null),
                            fn(Builder $query) => $query->where('notification_type_id', $data['value'])
                        );
                    }),

                SelectFilter::make('priority')
                    ->label('Filter by Priority')
                    ->placeholder('All')
                    ->options([
                        'High' => 'High',
                        'Medium' => 'Medium',
                        'Low' => 'Low',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['value'] ?? null),
                            fn(Builder $query) => $query->where('data->priority', $data['value'])
                        );
                    }),

            ])
            ->actions([

                Tables\Actions\Action::make('goToFullUrl')
                    ->url(fn($record): string => url($record->full_url))
                    ->icon('heroicon-s-arrow-long-right')
                    ->label('Visit')
                    ->color('success')
                    ->visible(fn(Notification $record): bool => filled($record->full_url))
                    ->openUrlInNewTab(),
                Tables\Actions\ViewAction::make('Read')
                    ->label('Read')
                    ->color('warning')
                    ->url(function (Notification $record) {
                        // dd($record);
                        return Pages\ViewNotificationSent::getUrl(['record' => $record->id]);
                    })->openUrlInNewTab(),
                Tables\Actions\Action::make('markAsUnread')
                    ->icon('heroicon-o-pause')
                    ->label('Mark as Unread')
                    ->color('success')
                    ->disabled(fn(Notification  $record): bool => $record->read_at === null)
                    ->action(function (Notification $record): void {
                        $record->update(['read_at' => null]);
                        NotificationHistory::create([
                            'notification_id' => $record->id,
                            'user_id' => auth()->user()->id,
                            'read_by' => auth()->user()->id,
                            'action' => 'unread',
                            'read_at' => now()
                        ]);
                        // Show Filament notification
                        \Filament\Notifications\Notification::make()
                            ->title('Success')
                            ->body('Notification marked as unread.')
                            ->success()
                            ->send();
                    }),

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
            NotificationHistoryRelationManager::class,

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
