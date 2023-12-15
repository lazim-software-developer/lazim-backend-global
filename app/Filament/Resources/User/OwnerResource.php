<?php

namespace App\Filament\Resources\User;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\User\User;
use App\Models\User\Owner;
use Filament\Tables\Table;
use App\Models\ApartmentOwner;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Filters\buildingFilter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\User\OwnerResource\Pages;
use App\Filament\Resources\User\OwnerResource\RelationManagers;
use App\Filament\Resources\User\OwnerResource\RelationManagers\UserDocumentsRelationManager;
use App\Models\Building\Flat;
use App\Models\FlatOwners;

class OwnerResource extends Resource
{
    protected static ?string $model = ApartmentOwner::class;
    protected static ?string $modelLabel = 'Owner';
    protected static ?string $navigationGroup = 'User Management';
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

                    TextInput::make('owner_number')
                        ->numeric()
                        ->required()
                        ->placeholder('Owner Number'),
                    TextInput::make('name')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Name'),
                    TextInput::make('mobile')
                        ->rules(['regex:/^(\+971)(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/'])
                        ->nullable()
                        ->placeholder('Mobile'),
                    TextInput::make('email')
                        ->rules(['min:6', 'max:30', 'regex:/^[a-z0-9.]+@[a-z]+\.[a-z]{2,}$/'])
                        ->required()
                        ->placeholder('Email'),
                    TextInput::make('passport')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Passport'),
                    TextInput::make('emirates_id')
                        ->numeric()
                        ->required()
                        ->placeholder('Emirates Id'),
                    Repeater::make('flatOwners')
                        ->relationship()
                        ->schema([
                            Select::make('flat_id')
                                ->relationship('flat', 'property_number')
                                ->preload()
                                ->searchable()
                                ->label('Unit Number'),
                            // ViewField::make('Building')
                            //     ->view('forms.components.building-name-owner')
                        ])
                        ->columnSpan([
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 2,
                        ])
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
                ViewColumn::make('Flat')->view('tables.columns.apartment-ownerflat'),
                ViewColumn::make('Building')->view('tables.columns.apartment-ownerbuilding')
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // Filter::make('created_at')
                //     ->form([
                //         Select::make('flat')
                //             ->searchable()
                //             ->options(function () {
                //                 return Flat::pluck('property_number', 'id');
                //             }),
                //     ])
                //     ->query(function (Builder $query, array $data): Builder {
                //         return $query
                //             ->when(
                //                 $flatowner = FlatOwners::where('flat_id',$data['flat'])->first()?->owner_id,
                //                 fn(Builder $query,$flatowner): Builder => $query->where('id', $flatowner),
                //             );
                //     })
            ])
            ->emptyStateActions([
                //Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //UserDocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOwners::route('/'),
            //'create' => Pages\CreateOwner::route('/create'),
            'view' => Pages\ViewOwner::route('/{record}'),
            // 'edit' => Pages\EditOwner::route('/{record}/edit'),
        ];
    }
}
