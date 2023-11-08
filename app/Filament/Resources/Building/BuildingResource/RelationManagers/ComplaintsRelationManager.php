<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class ComplaintsRelationManager extends RelationManager
{
    protected static string $relationship = 'complaints';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 0])->schema([
                    Hidden::make('complaintable_type')
                            ->default('App\Models\Building\FlatTenant'),
                        Hidden::make('complaintable_id')
                            ->default(1),
                        Hidden::make('owner_association_id')
                            ->default(auth()->user()->owner_association_id),
                        Select::make('building_id')
                            ->rules(['exists:buildings,id'])
                            ->relationship('building', 'name')
                            ->reactive()
                            ->preload()
                            ->searchable()
                            ->placeholder('Building'),
                        Select::make('user_id')
                            ->relationship('user', 'id')
                            ->options(function () {
                                $tenants = DB::table('flat_tenants')->pluck('tenant_id');
                                // dd($tenants);
                                return DB::table('users')
                                    ->whereIn('users.id', $tenants)
                                    ->select('users.id', 'users.first_name')
                                    ->pluck('users.first_name', 'users.id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('User'),
                        Select::make('category')
                            ->options([
                                'civil'    => 'Civil',
                                'MIP'      => 'MIP',
                                'security' => 'Security',
                                'cleaning' => 'Cleaning',
                                'others'   => 'Others',
                            ])
                            ->rules(['max:50', 'string'])
                            ->required()
                            ->searchable()
                            ->placeholder('Category'),
                        FileUpload::make('photo')
                            ->disk('s3')
                            ->directory('dev')
                            ->maxSize(2048)
                            ->image()
                            ->nullable(),
                        TextInput::make('complaint')
                            ->placeholder('Complaint'),
                        Hidden::make('status')
                            ->default('pending'),
                        Hidden::make('complaint_type')
                            ->default('help_desk'),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('building.name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('user.first_name')
                    ->toggleable()
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('category')
                    ->toggleable()
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('complaint')
                    ->toggleable()
                    ->default('NA')
                    ->searchable(),
                TextColumn::make('status')
                    ->toggleable()
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
            ])
            ->filters([
                //
            ]);
    }
}
