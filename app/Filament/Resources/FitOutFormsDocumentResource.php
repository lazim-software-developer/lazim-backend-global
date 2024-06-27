<?php

namespace App\Filament\Resources;

use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Tables;
use App\Models\Master\Role;
use App\Models\Forms\FitOutForm;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
use Filament\Tables\Actions\BulkAction;
use App\Filament\Resources\FitOutFormsDocumentResource\Pages;
use App\Filament\Resources\FitOutFormsDocumentResource\RelationManagers\ContractorRequestRelationManager;
use Closure;
use Filament\Forms\Components\FileUpload;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class FitOutFormsDocumentResource extends Resource
{
    protected static ?string $model = FitOutForm::class;

    protected static ?string $modelLabel = 'Fit out';
    protected static ?string $navigationGroup = 'Forms Document';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([
                            TextInput::make('contractor_name')
                                ->disabled()
                                ->label('Contractor Name'),
                            TextInput::make('email')
                                ->label('Contractor Email')
                                ->disabled()
                                ->placeholder('Email'),
                            TextInput::make('phone')
                                ->label('Contractor Phone Number')
                                ->disabled()
                                ->placeholder('Phone Number'),
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
                            Select::make('user_id')
                                ->rules(['exists:users,id'])
                                ->relationship('user', 'first_name')
                                ->disabled()
                                ->preload()
                                ->searchable()
                                ->label('User'),
                            Toggle::make('no_objection')->disabled(),
                            Toggle::make('undertaking_of_waterproofing')->disabled(),
                            Select::make('status')
                                ->options([
                                    'approved' => 'Approve',
                                    'rejected' => 'Reject',
                                ])
                                ->disabled(function(FitOutForm $record){

                                    return $record->status != null ;
                                })->visible(function(FitOutForm $record){

                                    return $record->contractorRequest?->exists();
                                })
                                ->required()
                                ->searchable()
                                ->live(),
                            TextInput::make('remarks')
                                ->rules(['max:150'])
                                ->visible(function (callable $get) {
                                    if ($get('status') == 'rejected') {
                                        return true;
                                    }
                                    return false;
                                })
                                ->disabled(function(FitOutForm $record){
                                    return $record->status != null;
                                })
                                ->required(),
                            FileUpload::make('admin_document')
                                ->disk('s3')
                                ->directory('dev')->required()
                                ->rules(['file','mimes:pdf',function () {
                                    return function (string $attribute, $value, Closure $fail) {
                                        if($value->getSize()/ 1024 > 2048){
                                            $fail('The document must not be greater than 2MB.');
                                        }
                                    };
                                },])
                                ->openable(true)
                                ->downloadable(true)
                                ->disabled(function(FitOutForm $record){

                                    return $record->admin_document  ;
                                })
                                ->visible(function (callable $get,$record) {
                                    if ($record->orders->first()?->payment_status == 'succeeded'  && $record->status == 'approved') {
                                        return true;
                                    }
                                    return false;
                                })->helperText('Once a document is uploaded, it cannot be modified.')
                                ->label('Document'),
                             // If the form is rejected, we need to capture which fields are rejected
                             CheckboxList::make('rejected_fields')
                                ->label('Please select rejected fields')
                                ->options([
                                    'contractor_name' => 'Contractor Name',
                                    'email' => 'Contractor email',
                                    'phone' => 'Contractor phone',
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
            ->columns([

                    TextColumn::make('ticket_number')
                    ->searchable()
                    ->default('NA')
                    ->label('Ticket Number'),
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
                IconColumn::make('no_objection')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),
                IconColumn::make('undertaking_of_waterproofing')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),

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
            ContractorRequestRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFitOutFormsDocuments::route('/'),
            // 'view' => Pages\ViewFitOutFormsDocument::route('/{record}'),
            'edit' => Pages\EditFitOutFormsDocument::route('/{record}/edit'),

        ];
    }
}
