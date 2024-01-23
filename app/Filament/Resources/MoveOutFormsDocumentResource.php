<?php

namespace App\Filament\Resources;

use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\Forms\MoveInOut;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
use App\Filament\Resources\MoveOutFormsDocumentResource\Pages;

class MoveOutFormsDocumentResource extends Resource
{
    protected static ?string $model = MoveInOut::class;
    protected static ?string $modelLabel = 'MoveOut';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Forms Document';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([
                    TextInput::make('name')->disabled(),
                    TextInput::make('email')->disabled(),
                    TextInput::make('phone')->disabled(),
                    TextInput::make('moving_date')->disabled(),
                    TextInput::make('moving_time')->disabled(),
                    Select::make('building_id')
                        ->relationship('building', 'name')
                        ->preload()
                        ->disabled()
                        ->searchable()
                        ->label('Building Name'),
                    Select::make('flat_id')
                        ->relationship('flat', 'property_number')
                        ->preload()
                        ->disabled()
                        ->searchable()
                        ->label('Unit Number'),
                    FileUpload::make('noc_landlord')
                        ->visible(function (callable $get) {
                            if ($get('noc_landlord') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->disk('s3')
                        ->directory('dev')
                        ->downloadable(true)
                        ->openable(true)
                        ->disabled()
                        ->previewable(true)
                        ->label('NOC Landlord'),
                    FileUpload::make('cooling_final')
                        ->visible(function (callable $get) {
                            if ($get('cooling_final') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->disk('s3')
                        ->directory('dev')
                        ->downloadable(true)
                        ->disabled()
                        ->previewable(true)
                        ->openable(true)
                        ->label('Cooling Final Bill'),
                    FileUpload::make('contract')
                        ->visible(function (callable $get) {
                            if ($get('contract') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Tenancy / Ejari'),
                    FileUpload::make('gas_final')
                        ->visible(function (callable $get) {
                            if ($get('gas_final') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Gas Final Bill'),
                    FileUpload::make('gas_clearance')
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Gas Clearance'),
                    FileUpload::make('cooling_clearance')
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Cooling Clearance'),
                    FileUpload::make('dewa_final')
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Dewa Final Bill'),
                    FileUpload::make('etisalat_final')
                        ->visible(function (callable $get) {
                            if ($get('etisalat_final') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Etisalat Final Bill'),
                    FileUpload::make('movers_license')
                        ->visible(function (callable $get) {
                            if ($get('movers_license') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label("Movers ID's and Company License"),
                    FileUpload::make('movers_liability')
                        ->visible(function (callable $get) {
                            if ($get('movers_liability') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Movers Third Party Liability/Security Deposit'),
                    Select::make('status')
                        ->options([
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ])
                        ->disabled(function (MoveInOut $record) {
                            return $record->status != null;
                        })
                        ->required()
                        ->searchable()
                        ->live(),
                    TextInput::make('remarks')
                        ->rules(['max:255'])
                        ->visible(function (callable $get) {
                            if ($get('status') == 'rejected') {
                                return true;
                            }
                            return false;
                        })
                        ->disabled(function (MoveInOut $record) {
                            return $record->status != null;
                        })
                        ->required(),
                    // If the form is rejected, we need to capture which fields are rejected
                    CheckboxList::make('rejected_fields')
                        ->label('Please select rejected fields')
                        ->options([
                            'noc_landlord' => 'Handover Acceptance',
                            'cooling_final' => 'Receipt charges',
                            'contract' => 'Tenancy / Ejari',
                            'movers_license' => 'Movers license',
                            'movers_liability' => 'Movers liability',
                            'etisalat_final' => 'Etisalat final bill',
                            'dewa_final' => 'Dewa final bill',
                            'gas_clearance' => 'Gas clearance',
                            'cooling_clearance' => 'Cooling clearance',
                            'gas_final' => 'Gas final bill',
                            'cooling_final' => 'Cooling final bill',
                            'noc_landlord' => 'NOC landlord',
                        ])->columns(4)
                        ->visible(function (callable $get) {
                            if ($get('status') == 'rejected') {
                                return true;
                            }
                            return false;
                        }),

                ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'move-out')->withoutGlobalScopes())
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->default('NA')
                    ->label('Unit Number')
                    ->limit(50),
                TextColumn::make('status')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('remarks')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->relationship('building', 'name', function (Builder $query) {
                        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                            $query->where('owner_association_id', auth()->user()->owner_association_id);
                        }

                    })
                    ->searchable()
                    ->preload()
                    ->label('Building'),
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListMoveOutFormsDocuments::route('/'),
            // 'view' => Pages\ViewMoveOutFormsDocument::route('/{record}'),
            'edit' => Pages\EditMoveOutFormsDocument::route('/{record}/edit'),
        ];
    }
}
