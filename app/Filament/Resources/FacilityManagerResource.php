<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BuildingsRelationManagerResource\RelationManagers\BuildingsRelationManager;
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
    protected static ?string $model = Vendor::class;

    protected static ?string $modelLabel = 'Facility Manager';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Vendor Registration')
                    ->schema([
                        Select::make('owner_association_id')
                            ->label('Select OA')
                            ->relationship('ownerAssociation', 'name')
                            ->required(),
                        TextInput::make('name')
                            ->label('Company Name')
                            ->required(),
                        TextInput::make('user.email')
                            ->label('Email')
                            ->required()
                            ->email()
                            ->rules(['required', 'email', 'min:6', 'max:30',
                                'regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'])
                            ->unique(
                                table: User::class,
                                column: 'email',
                                ignorable: fn($record) => $record?->user
                            )
                            ->disabledOn('edit'),
                        TextInput::make('user.phone')
                            ->label('Phone Number')
                            ->required()
                            ->tel()
                            ->rules(['required', 'regex:/^(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/'])
                            ->unique(
                                table: User::class,
                                column: 'phone',
                                ignorable: fn($record) => $record?->user
                            )
                            ->disabledOn('edit')
                            ->prefix('971'),
                    ]),

                Section::make('Company Details')
                    ->schema([
                        TextInput::make('address_line_1')
                            ->label('Company Address')
                            ->required(),
                        TextInput::make('landline_number')
                            ->label('Company Landline Number')
                            ->required()
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
                        DatePicker::make('tl_expiry')
                            ->label('Company Trade License Expiry Date')
                            ->required(),
                        DatePicker::make('risk_policy_expiry')
                            ->label('Risk Policy Expiry Date')
                            ->required(),
                        Select::make('status')
                            ->visibleOn('edit')
                            ->options([
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ]),
                    ]),

                Section::make('Manager Details')
                    ->schema([
                        TextInput::make('managers.0.name')
                            ->label('Authorized Manager Name')
                            ->rules(['nullable', 'string']),
                        TextInput::make('managers.0.email')
                            ->label('Authorized Manager Email')
                            ->email()
                            ->rules(['nullable', 'email']),
                        TextInput::make('managers.0.phone')
                            ->label('Authorized Manager Phone Number')
                            ->tel()
                            ->rules(['nullable']),
                    ])
                    ->collapsed()
                    ->collapsible(),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Company Name')->searchable(),
                Tables\Columns\TextColumn::make('user.email')->label('Company Email')->searchable(),
                Tables\Columns\TextColumn::make('status')->label('Approval Status')->searchable(),

                // Tables\Columns\TextColumn::make('user.phone')->searchable(),
                // Tables\Columns\TextColumn::make('tl_number')->label('Trade License Number')->searchable(),
                // Tables\Columns\TextColumn::make('tl_expiry')->label('Trade License Expiry')->date(),
            ])
            ->defaultSort('created_at', 'desc')
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
            BuildingsRelationManager::class,
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

    // public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    // {
    //     return parent::getEloquentQuery()->whereHas('user.roles', function ($query) {
    //         $query->where('name', 'Facility Manager');
    //     });
    // }
}
