<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MoveInFormsDocumentResource\Pages;
use App\Filament\Resources\MoveInFormsDocumentResource\RelationManagers;
use App\Models\Building\Document;
use App\Models\Forms\MoveInOut;
use App\Models\MoveInFormsDocument;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

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
                    TextInput::make('name'),
            Select::make('building_id')
                ->relationship('building','name')
                ->preload()
                ->searchable()
                ->label('Building Name'),
            Select::make('flat_id')
                ->relationship('flat','property_number')
                ->preload()
                ->searchable()
                ->label('Property No'),
            Select::make('user_id')
                ->rules(['exists:users,id'])
                ->relationship('user', 'first_name')
                ->required()
                ->preload()
                ->searchable()
                ->label('User'),
            TextInput::make('status')
                ->required()
                ->label('Status'),
            TextInput::make('remarks')
                ->required()
                ->label('Remarks'),

                FileUpload::make('handover_acceptance')
                    ->disk('s3')
                    ->directory('dev')
                    ->downloadable()
                    ->openable()
                    ->label('Handover Acceptance'),
                FileUpload::make('receipt_charges')
                    ->disk('s3')
                    ->directory('dev')
                    ->downloadable()
                    ->openable()
                    ->label('Receipt Charges'),
                FileUpload::make('contract')
                    ->disk('s3')
                    ->directory('dev')
                    ->downloadable()
                    ->openable()
                    ->label('Contract'),
                FileUpload::make('title_deed')
                    ->disk('s3')
                    ->directory('dev')
                    ->downloadable()
                    ->openable()
                    ->label('Title Deed'),
                FileUpload::make('passport')
                    ->disk('s3')
                    ->directory('dev')
                    ->downloadable()
                    ->openable()
                    ->label('Passport'),
                FileUpload::make('dewa')
                    ->disk('s3')
                    ->directory('dev')
                    ->downloadable()
                    ->openable()
                    ->label('Dewa'),
                FileUpload::make('cooling_registration')
                    ->disk('s3')
                    ->directory('dev')
                    ->downloadable()
                    ->openable()
                    ->label('Cooling Registration'),
                FileUpload::make('gas_registration')
                    ->disk('s3')
                    ->directory('dev')
                    ->downloadable()
                    ->openable()
                    ->label('Gas Registration'),
                FileUpload::make('vehicle_registration')
                    ->disk('s3')
                    ->directory('dev')
                    ->downloadable()
                    ->openable()
                    ->label('Vehicle Registration'),
                FileUpload::make('movers_license')
                    ->disk('s3')
                    ->directory('dev')
                    ->downloadable()
                    ->openable()
                    ->label('Movers License'),
                FileUpload::make('movers_liability')
                    ->disk('s3')
                    ->directory('dev')
                    ->downloadable()
                    ->openable()
                    ->label('Movers Liability'),
                    
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
                TextColumn::make('user.first_name')
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
                ImageColumn::make('handover_acceptance')
                    ->disk('s3')
                    ->circular(),
                ImageColumn::make('receipt_charges')
                    ->square()
                    ->disk('s3'),
                ImageColumn::make('contract')
                    ->square()
                    ->disk('s3'),
                ImageColumn::make('title_deed')
                    ->circular()
                    ->disk('s3'),
                ImageColumn::make('passport')
                    ->square()
                    ->disk('s3'),
                ImageColumn::make('dewa')
                    ->circular()
                    ->disk('s3'),
                ImageColumn::make('cooling_registration')
                    ->square()
                    ->disk('s3'),
                ImageColumn::make('gas_registration')
                    ->square()
                    ->disk('s3'),
                ImageColumn::make('vehicle_registration')
                    ->square()
                    ->disk('s3'),
                ImageColumn::make('movers_license')
                    ->square()
                    ->disk('s3'),
                ImageColumn::make('movers_liability')
                    ->square()
                    ->disk('s3'),

            ])
            ->filters([
                //
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
                Action::make('Update Status')
                    ->visible(fn ($record) => $record->status === null)
                    ->button()
                    ->form([
                        Select::make('status')
                            ->options([
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->searchable()
                            ->live(),
                        TextInput::make('remarks')
                            ->rules(['max:255'])
                            ->visible(function (callable $get) {
                                if ($get('status') == 'rejected') {
                                    return true;
                                }
                                return false;
                            }),
                    ])
                    ->fillForm(fn (MoveInOut $record): array => [
                        'status' => $record->status,
                        'remarks' => $record->remarks,
                    ])
                    ->action(function (MoveInOut $record, array $data): void {
                        if ($data['status'] == 'rejected') {
                            $record->status = $data['status'];
                            $record->remarks = $data['remarks'];
                            $record->save();
                        } else {
                            $record->status = $data['status'];
                            $record->save();
                        }
                    })
                    ->slideOver()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListMoveInFormsDocuments::route('/'),
            //'create' => Pages\CreateMoveInFormsDocument::route('/create'),
            //'edit' => Pages\EditMoveInFormsDocument::route('/{record}/edit'),
            'view' => Pages\ViewMoveInFormsDocument::route('/{record}'),
        ];
    }
}
