<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MoveInFormsDocumentResource\Pages;
use App\Filament\Resources\MoveInFormsDocumentResource\Pages\CreateMoveInFormsDocument;
use App\Models\Forms\MoveInOut;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class MoveInFormsDocumentResource extends Resource
{
    protected static ?string $model = MoveInOut::class;
    protected static ?string $modelLabel = 'MoveIn';
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
                        ->label('Property No'),
                    FileUpload::make('handover_acceptance')
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Handover Acceptance'),
                    FileUpload::make('receipt_charges')
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Receipt Charges'),
                    FileUpload::make('contract')
                        ->disk('s3')
                        ->directory('dev')
                        ->downloadable(true)
                        ->disabled()
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
                        ->label('Passport'),
                    FileUpload::make('dewa')
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Dewa'),
                    FileUpload::make('cooling_registration')
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Cooling Registration'),
                    FileUpload::make('gas_registration')
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Gas Registration'),
                    FileUpload::make('vehicle_registration')
                        ->disk('s3')
                        ->directory('dev')
                        ->downloadable(true)
                        ->disabled()
                        ->openable(true)
                        ->label('Vehicle Registration'),
                    FileUpload::make('movers_license')
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Movers License'),
                    FileUpload::make('movers_liability')
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
                            'etisalat_final' => 'Mtisalat final',
                            'dewa_final' => 'Dewa final',
                            'gas_clearance' => 'Gas clearance',
                            'cooling_clearance' => 'Cooling clearance',
                            'gas_final' => 'Gas final',
                            'cooling_final' => 'Cooling final',
                            'noc_landlord' => 'NOC landlord',
                        ])->columns(3)
                        ->visible(function (callable $get) {
                            if ($get('status') == 'rejected') {
                                return true;
                            }
                            return false;
                        })
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'move-in')->withoutGlobalScopes())
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
                //
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
