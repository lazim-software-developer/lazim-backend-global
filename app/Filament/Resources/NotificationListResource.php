<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Notification;
use App\Models\Forms\SaleNOC;
use Pages\ViewNotificationSent;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use App\Models\NotificationSents;
use Filament\Widgets\TableWidget;
use App\Models\NotificationHistory;
use App\Models\SaleNocNotification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Illuminate\Database\Eloquent\Builder;
use App\Models\SaleNocNotificationHistory;
use App\Filament\Resources\NotificationListResource\Pages;

class NotificationListResource extends Resource
{
    protected static ?string $model = Notification::class;
    protected static ?string $modelLabel      = 'Notifications';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                TextInput::make('custom_json_data.building_id')
                    ->label('Building')
                    ->formatStateUsing(fn($state): string => Building::where('id', $state)->value('name') ?: 'NA')
                    ->placeholder('NA')
                ->disabled(),
            TextInput::make('custom_json_data.user_id')
                ->label('User')
                ->formatStateUsing(fn($state): string => User::where('id', $state)->value('first_name') ?: 'NA')
                ->placeholder('NA')
                ->disabled(),
            TextInput::make('data.title')->required(),
            Textarea::make('data.body')->required(),
            Section::make('History')
                ->hidden(fn(?Notification $record): bool => $record === null)
                ->description('Notification history records')
                ->schema([
                    ViewField::make('history')
                        ->view('filament.pages.notification-history-table')
                        ->viewData([
                            'recordId' => static::getModel()::find(request()->route('record'))->id,
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('building_id')
                    ->formatStateUsing(fn($state): string => Building::where('id', $state)->value('name'))
                    // ->searchable()
                    ->label('Building'),
                TextColumn::make('user_id')
                    ->formatStateUsing(fn($state): string => User::where('id', $state)->value('first_name'))
                    // ->searchable()
                    ->label('User'),
                TextColumn::make('title')
                    // ->searchable()
                    ->label('Notification Title'),
                TextColumn::make('read_at')
                    // ->searchable()
                    ->label('Read At'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // Add building filter here
                Tables\Filters\SelectFilter::make('building_id')
                    ->label('Filter by Building')
                    ->options(
                        Building::where('owner_association_id', auth()->user()?->owner_association_id)
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $buildingId): Builder => $query->where('custom_json_data->building_id', $buildingId)
                        );
                    }),
                    Tables\Filters\SelectFilter::make('priority')
                    ->label('Filter by Priority')
                    ->options([
                        'High' => 'High',
                        'Medium' => 'Medium',
                        'Low' => 'Low',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], fn (Builder $query, $priority): Builder => $query->where('custom_json_data->priority', $priority));
                    }),
                    Tables\Filters\SelectFilter::make('type')
                    ->label('Filter by Type')
                    ->options([
                        'Complaint' => 'Complaint',
                        'SaleNoc' => 'SaleNoc',
                        'ServiceBooking' => 'Service Booking',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], fn (Builder $query, $type): Builder => $query->where('custom_json_data->type', $type));
                    })
            ])
            ->actions([
                Tables\Actions\Action::make('goToFullUrl')
                    ->url(fn($record): string => url($record->full_url))
                    ->icon('heroicon-s-arrow-long-right')
                    ->label('Visit')
                    ->color('success')
                    ->visible(fn(Notification $record): bool => filled($record->full_url))
                    ->openUrlInNewTab(),
                Tables\Actions\ViewAction::make('Read')->label('Read')->color('warning')
                ->url(function ($record) {
                    // dd($record);
                    return Pages\ViewNotificationSent::getUrl(['record' => $record->id]);
                }),
                Tables\Actions\Action::make('markAsUnread')
                    ->icon('heroicon-o-pause')
                    ->label('Mark as Unread')
                    ->color('success')
                    ->disabled(fn(Notification $record): bool => $record->read_at === null)
                    ->action(function (Notification $record): void {
                        $record->update(['is_read' => false, 'read_by' => null, 'read_at' => null]);
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

                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
