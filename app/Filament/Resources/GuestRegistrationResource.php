<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuestRegistrationResource\Pages;
use App\Filament\Resources\GuestRegistrationResource\RelationManagers;
use App\Models\Forms\Guest;
use App\Models\GuestRegistration;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use function Laravel\Prompts\select;

class GuestRegistrationResource extends Resource
{
    protected static ?string $model = Guest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('passport_number'),
                DatePicker::make('visa_validity_date'),
                DatePicker::make('expiry_date'),
                TextInput::make('stay_duration'),
                FileUpload::make('dtmc_license_url')
                    ->disk('s3')
                    ->directory('dev')
                    ->label('Dtmc License')
                    ->required(),
                Hidden::make('access_card_holder')
                    ->default(1),
                Hidden::make('original_passport')
                    ->default(1),
                Hidden::make('guest_registration')
                    ->default(1),

                select::make('flat_visitor_id')
                    ->relationship('flatVisitor','name')
                    ->createOptionForm([
                        Select::make('building_id')
                            ->relationship('building','name')
                            ->preload()
                            ->searchable()
                            ->label('Building Name'),
                        Select::make('flat_id')
                            ->relationship('flat','property_number')
                            ->preload()
                            ->searchable()
                            ->label('Property No'),
                        TextInput::make('name'),
                        TextInput::make('phone'),
                        Hidden::make('type')
                            ->default('Guest'),
                        Hidden::make('initiated_by')
                            ->default(auth()->user()->id),
                        TextInput::make('email'),
                        DatePicker::make('start_time')
                            ->label('From Date'),
                        DatePicker::make('end_time')
                            ->label('To Date'),
                        TextInput::make('number_of_visitors'),
                    ])
                    ->editOptionForm([
                        Select::make('building_id')
                            ->relationship('building','name')
                            ->preload()
                            ->searchable()
                            ->label('Building Name'),
                        Select::make('flat_id')
                            ->relationship('flat','property_number')
                            ->preload()
                            ->searchable()
                            ->label('Property No'),
                        TextInput::make('name'),
                        TextInput::make('phone'),
                        Hidden::make('type')
                            ->default('Guest'),
                        Hidden::make('initiated_by')
                            ->default(auth()->user()->id),
                        Hidden::make('approved_by')
                            ->default(auth()->user()->id),
                        TextInput::make('email'),
                        DatePicker::make('start_time')
                            ->label('From Date'),
                        DatePicker::make('end_time')
                            ->label('To Date'),
                        TextInput::make('number_of_visitors'),
                    ])
                    ->label('Flat Visitor'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('passport_number')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('visa_validity_date')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('expiry_date')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('stay_duration')
                    ->searchable()
                    ->default('NA'),

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
            'index' => Pages\ListGuestRegistrations::route('/'),
            'create' => Pages\CreateGuestRegistration::route('/create'),
            'edit' => Pages\EditGuestRegistration::route('/{record}/edit'),
        ];
    }    
}
