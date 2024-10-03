<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FacilityManagerResource\Pages;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FacilityManagerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Facility Manager';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Vendor Registration')
                    ->schema([
                        Select::make('oa_id')
                            ->label('Select OA')
                            ->relationship('ownerAssociation', 'name')
                            ->required(),
                        TextInput::make('company_name')
                            ->label('Company Name')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->required()
                            ->rules(['required', 'email', 'min:6', 'max:30',
                                'regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'])
                            ->unique(ignoreRecord: true)
                            ->disabledOn('edit'),
                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->required()
                            ->rules(['required', 'regex:/^(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/'])
                            ->unique(ignoreRecord: true)
                            ->disabledOn('edit')
                            ->prefix('971'),
                    ]),

                Section::make('Company Details')
                    ->schema([
                        TextInput::make('name')
                            ->label('Company Name')
                            ->required(),
                        TextInput::make('address')
                            ->label('Company Address')
                            ->required(),
                        TextInput::make('landline')
                            ->label('Company Landline Number')
                            ->tel(),
                        TextInput::make('website')
                            ->label('Company Website')
                            ->url(),
                        TextInput::make('fax')
                            ->label('Company Fax Number'),
                        TextInput::make('tl_number')
                            ->label('Company Trade License Number')
                            ->required()
                            ->rules(['required', 'max:50', 'string'])
                            ->unique(Vendor::class, 'tl_number', ignoreRecord: true),
                        DatePicker::make('trade_license_expiry')
                            ->label('Company Trade License Expiry Date')
                            ->required(),
                        DatePicker::make('risk_policy_expiry')
                            ->label('Risk Policy Expiry Date')
                            ->required(),
                    ]),

                Section::make('Manager Details')
                    ->schema([
                        TextInput::make('manager_name')
                            ->label('Authorized Manager Name'),
                        TextInput::make('manager_email')
                            ->label('Authorized Manager Email')
                            ->email(),
                        TextInput::make('manager_phone')
                            ->label('Authorized Manager Phone Number')
                            ->tel(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('vendors.name')->label('Company Name')->searchable(),
                Tables\Columns\TextColumn::make('vendors.tl_number')->label('Trade License Number')->searchable(),
                Tables\Columns\TextColumn::make('vendors.tl_expiry')->label('Trade License Expiry')->date(),
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
            'index'  => Pages\ListFacilityManagers::route('/'),
            'create' => Pages\CreateFacilityManager::route('/create'),
            'edit'   => Pages\EditFacilityManager::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->whereHas('roles', function ($query) {
            $query->where('name', 'Facility Manager');
        });
    }
}
