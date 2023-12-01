<?php

namespace App\Filament\Resources\User;

use App\Filament\Resources\User\TenantResource\Pages;
use App\Filament\Resources\User\TenantResource\RelationManagers;
use App\Filament\Resources\User\TenantResource\RelationManagers\UserDocumentsRelationManager;
use App\Models\MollakTenant;
use App\Models\User\Tenant;
use App\Models\User\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TenantResource extends Resource
{
    protected static ?string $model = MollakTenant::class;
    protected static ?string $modelLabel      = 'Tenant';
    protected static ?string $navigationGroup      = 'User Management';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make([
                'sm' => 1,
                'md' => 1,
                'lg' => 2
            ])
                ->schema([
                    TextInput::make('name')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Name'),
                    TextInput::make('contract_number')
                        ->numeric()
                        ->required()
                        ->placeholder('Contract Number'),
                    TextInput::make('emirates_id')
                        ->numeric()
                        ->required()
                        ->placeholder('Emirates Id'),
                    TextInput::make('mobile')
                        ->rules(['regex:/^(\+971)(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/'])
                        ->required()
                        ->placeholder('Mobile'),
                    TextInput::make('email')
                        ->rules(['min:6', 'max:30', 'regex:/^[a-z0-9.]+@[a-z]+\.[a-z]{2,}$/'])
                        ->required()
                        ->label('Email'),
                    Select::make('flat_id')
                        ->rules(['exists:flats,id'])
                        ->required()
                        ->relationship('flat', 'property_number')
                        ->searchable()
                        ->preload()
                        ->placeholder('Flat'),
                    Select::make('building_id')
                        ->rules(['exists:buildings,id'])
                        ->relationship('building', 'name')
                        ->reactive()
                        ->preload()
                        ->searchable()
                        ->placeholder('Building'),
                    DatePicker::make('start_date')
                        ->rules(['date'])
                        ->required()
                        ->placeholder('Start Date'),
                    DatePicker::make('end_date')
                        ->rules(['date'])
                        ->placeholder('End Date'),
                    Select::make('contract_status')
                        ->options([
                            'pass auditing' => 'Pass Auditing',
                            'active' => 'Active',
                            'under auditing' => 'Under Auditing'
                        ])
                        ->searchable()
                        ->live(),
                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->default('NA')
                    ->label('Name')
                    ->limit(50),
                Tables\Columns\TextColumn::make('mobile')
                    ->searchable()
                    ->default('NA')
                    ->label('Mobile')
                    ->limit(50),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->default('NA')
                    ->label('Email')
                    ->limit(50),
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA')
                    ->label('Buildings'),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->default('NA')
                    ->label('Flats'),
                Tables\Columns\TextColumn::make('contract_status')
                    ->searchable()
                    ->default('NA')
                    ->label('Contract Status')
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->relationship('building', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Building'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                //Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // UserDocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            //'create' => Pages\CreateTenant::route('/create'),
            'view' => Pages\ViewTenant::route('/{record}'),
        ];
    }
}
