<?php
namespace App\Filament\Resources;

use DB;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\User\User;
use App\Models\FlatOwners;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\RentalDetail;
use App\Models\UserApproval;
use App\Models\Building\Flat;
use App\Models\ApartmentOwner;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use FilamentTiptapEditor\TiptapEditor;
use App\Models\InvoiceReminderTracking;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\EmailReminderTracking\Pages;

class EmailReminderHistoryResource extends Resource
{
    protected static ?string $model      = InvoiceReminderTracking::class;
    protected static ?string $modelLabel = 'Email Reminder History';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $isScopedToTenant  = false;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->formatStateUsing(fn($state): string => User::where('id', $state)->value('first_name'))
                    ->searchable()
                    ->label('User Name'),
                Tables\Columns\TextColumn::make('user_email')->searchable(),
                Tables\Columns\TextColumn::make('building_id')
                    ->formatStateUsing(fn($state): string => Building::where('id', $state)->value('name'))
                    ->searchable()
                    ->label('Building'),
                Tables\Columns\TextColumn::make('flat_id')
                    ->formatStateUsing(fn($state): string => Flat::where('id', $state)->value('property_number'))
                    ->searchable()
                    ->label('Flat'),
                Tables\Columns\TextColumn::make('invoice_number')->searchable(),
                Tables\Columns\TextColumn::make('invoice_amount'),
                Tables\Columns\TextColumn::make('created_at')
                    ->date('d-M-Y'),
            ])
            ->filters([
                //
            ])
            ->actions([
            ])
            ->bulkActions([
            ]);
    }
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEmailReminderTracking::route('/'),
        ];
    }
}