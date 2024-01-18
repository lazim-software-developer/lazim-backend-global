<?php

namespace App\Filament\Resources;

use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\Forms\MoveInOut;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
use App\Filament\Resources\MoveInFormsDocumentResource\Pages;

class MoveInFormsDocumentResource extends Resource
{
    protected static ?string $model = MoveInOut::class;
    protected static ?string $modelLabel = 'Move in';
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
                        ->searchable()
                        ->disabled()
                        ->label('Building Name'),
                    Select::make('flat_id')
                        ->relationship('flat', 'property_number')
                        ->preload()
                        ->disabled()
                        ->searchable()
                        ->label('Unit Number'),
                    FileUpload::make('handover_acceptance')
                        ->disk('s3')
                        ->directory('dev')
                        ->downloadable(true)
                        ->openable(true)
                        ->visible(function (callable $get) {
                            if ($get('handover_acceptance') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->disabled()
                        ->label('Handover Acceptance'),
                    FileUpload::make('receipt_charges')
                        ->disk('s3')
                        ->directory('dev')
                        ->visible(function (callable $get) {
                            if ($get('receipt_charges') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Receipt Charges'),
                    FileUpload::make('contract')
                        ->disk('s3')
                        ->directory('dev')
                        ->downloadable(true)
                        ->disabled()
                        ->visible(function (callable $get) {
                            if ($get('contract') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->openable(true)
                        ->label('Contract'),
                    FileUpload::make('title_deed')
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Title Deed'),
                    FileUpload::make('passport')
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Passport / EID /Visa'),
                    FileUpload::make('dewa')
                        ->visible(function (callable $get) {
                            if ($get('dewa') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Dewa'),
                    FileUpload::make('cooling_registration')
                        ->visible(function (callable $get) {
                            if ($get('cooling_registration') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Cooling Registration'),
                    FileUpload::make('gas_registration')
                        ->visible(function (callable $get) {
                            if ($get('gas_registration') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Gas Registration'),
                    FileUpload::make('vehicle_registration')
                        ->visible(function (callable $get) {
                            if ($get('vehicle_registration') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->disk('s3')
                        ->directory('dev')
                        ->downloadable(true)
                        ->disabled()
                        ->openable(true)
                        ->label('Vehicle Registration'),
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
                        ->label('Movers License'),
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
                        ->label('Movers Liability'),
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
                            'handover_acceptance' => 'Handover Acceptance',
                            'receipt_charges' => 'Receipt charges',
                            'contract' => 'Contract',
                            'title_deed' => 'Title deed',
                            'passport' => 'Passport',
                            'dewa' => 'Dewa',
                            'cooling_registration' => 'Cooling registration',
                            'gas_registration' => 'Gas registration',
                            'vehicle_registration' => 'Vehicle registration',
                            'movers_license' => 'Movers license',
                            'movers_liability' => 'Movers liability',
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
            ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'move-in')->withoutGlobalScopes())
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

    protected function getRejectedFields($livewire)
    {
        Log::info("SHILPA");
        $record = $livewire->record; // Get the current record
        if ($record && $record->rejected_fields) {
            return json_decode($record->rejected_fields, true);
        }
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMoveInFormsDocuments::route('/'),
            // 'create' => CreateMoveInFormsDocument::route('/create'),
            // 'view' => Pages\ViewMoveInFormsDocument::route('/{record}'),
            'edit' => Pages\EditMoveInFormsDocument::route('/{record}/edit'),
        ];
    }
}
