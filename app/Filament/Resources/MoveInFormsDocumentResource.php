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
use App\Models\User\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class MoveInFormsDocumentResource extends Resource
{
    protected static ?string $model = MoveInOut::class;
    protected static ?string $modelLabel = 'Move in';
    protected static ?string $pluralModelLabel = 'Move in';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Forms Document';
    public static function form(Form $form): Form
    {
        return $form
    ->schema([

        // Personal Details Section
        Section::make('Personal Details')
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
                        ->label('Building'),
                    Select::make('flat_id')
                        ->relationship('flat', 'property_number')
                        ->preload()
                        ->disabled()
                        ->searchable()
                        ->label('Unit Number'),
                ]),
            ]),

        // Document Uploads Section
        Section::make('Documents')
            ->columns(2)
            ->schema([
                FileUpload::make('handover_acceptance')
                    ->disk('s3')
                    ->directory('dev')
                    ->downloadable(true)
                    ->openable(true)
                    ->visible(function (callable $get) {
                        return $get('handover_acceptance') != null;
                    })
                    ->disabled()
                    ->label('Handover Acceptance'),

                FileUpload::make('receipt_charges')
                    ->disk('s3')
                    ->directory('dev')
                    ->visible(function (callable $get) {
                        return $get('receipt_charges') != null;
                    })
                    ->disabled()
                    ->downloadable(true)
                    ->openable(true)
                    ->label('Paid Receipt of Service Charges'),

                FileUpload::make('contract')
                    ->disk('s3')
                    ->directory('dev')
                    ->downloadable(true)
                    ->disabled()
                    ->visible(function (callable $get) {
                        return $get('contract') != null;
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
                    ->label('Passport / EID / Visa'),

                FileUpload::make('dewa')
                    ->disk('s3')
                    ->directory('dev')
                    ->visible(function (callable $get) {
                        return $get('dewa') != null;
                    })
                    ->disabled()
                    ->downloadable(true)
                    ->openable(true)
                    ->label('Dewa Application'),

                FileUpload::make('cooling_registration')
                    ->disk('s3')
                    ->directory('dev')
                    ->visible(function (callable $get) {
                        return $get('cooling_registration') != null;
                    })
                    ->disabled()
                    ->downloadable(true)
                    ->openable(true)
                    ->label('Cooling Registration'),

                FileUpload::make('gas_registration')
                    ->disk('s3')
                    ->directory('dev')
                    ->visible(function (callable $get) {
                        return $get('gas_registration') != null;
                    })
                    ->disabled()
                    ->downloadable(true)
                    ->openable(true)
                    ->label('Gas Registration'),

                FileUpload::make('vehicle_registration')
                    ->disk('s3')
                    ->directory('dev')
                    ->visible(function (callable $get) {
                        return $get('vehicle_registration') != null;
                    })
                    ->disabled()
                    ->downloadable(true)
                    ->openable(true)
                    ->label('Vehicle Registration / Mulkiya'),

                FileUpload::make('movers_license')
                    ->disk('s3')
                    ->directory('dev')
                    ->visible(function (callable $get) {
                        return $get('movers_license') != null;
                    })
                    ->disabled()
                    ->downloadable(true)
                    ->openable(true)
                    ->label("Movers ID's and Company License"),

                FileUpload::make('movers_liability')
                    ->disk('s3')
                    ->directory('dev')
                    ->visible(function (callable $get) {
                        return $get('movers_liability') != null;
                    })
                    ->disabled()
                    ->downloadable(true)
                    ->openable(true)
                    ->label('Movers Third Party Liability/Security Deposit'),
            ]),

        // Approval Section
        Section::make('Approval Details')
            ->columns(2)
            ->schema([
                Select::make('status')
                    ->options([
                        'approved' => 'Approve',
                        'rejected' => 'Reject',
                    ])
                    ->disabled(function (MoveInOut $record) {
                        return $record->status != null;
                    })
                    ->required()
                    ->searchable()
                    ->live(),

                TextInput::make('remarks')
                    ->rules(['max:150'])
                    ->visible(function (callable $get) {
                        return $get('status') == 'rejected';
                    })
                    ->disabled(function (MoveInOut $record) {
                        return $record->status != null;
                    })
                    ->required(),

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
                    ])
                    ->columns(4)
                    ->visible(function (callable $get) {
                        return $get('status') == 'rejected';
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
                TextColumn::make('ticket_number')
                ->searchable()
                ->default('NA')
                ->label('Ticket Number'),
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
                    ->label('Unit number')
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
                            $query->where('owner_association_id', Filament::getTenant()?->id);
                        }

                    })
                    ->searchable()
                    ->preload()
                    ->label('Building'),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
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
    public static function canViewAny(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_any_move::in::forms::document');
    }

    public static function canView(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_move::in::forms::document');
    }

    public static function canCreate(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('create_move::in::forms::document');
    }

    public static function canEdit(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('update_move::in::forms::document');
    }
}
