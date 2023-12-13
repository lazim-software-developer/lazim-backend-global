<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MoveOutFormsDocumentResource\Pages;
use App\Filament\Resources\MoveOutFormsDocumentResource\RelationManagers;
use App\Models\Forms\MoveInOut;
use App\Models\MoveOutFormsDocument;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                                ->disk('s3')
                                ->directory('dev')
                                ->downloadable(true)
                                ->openable(true)
                                ->disabled()
                                ->previewable(true)
                                ->label('NOC Landlord'),
                            FileUpload::make('cooling_final')
                                ->disk('s3')
                                ->directory('dev')
                                ->downloadable(true)
                                ->disabled()
                                ->previewable(true)
                                ->openable(true)
                                ->label('Cooling Final Bill'),
                            FileUpload::make('contract')
                                ->disk('s3')
                                ->directory('dev')
                                ->disabled()
                                ->downloadable(true)
                                ->openable(true)
                                ->label('Tenancy / Ejari'),
                            FileUpload::make('gas_final')
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
                                ->disk('s3')
                                ->directory('dev')
                                ->disabled()
                                ->downloadable(true)
                                ->openable(true)
                                ->label('Etisalat Final Bill'),
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
                                ->disabled(function(MoveInOut $record){
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
                                ->disabled(function(MoveInOut $record){
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
                                })

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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMoveOutFormsDocuments::route('/'),
            // 'view' => Pages\ViewMoveOutFormsDocument::route('/{record}'),
            'edit'   => Pages\EditMoveOutFormsDocument::route('/{record}/edit'),
        ];
    }
}
