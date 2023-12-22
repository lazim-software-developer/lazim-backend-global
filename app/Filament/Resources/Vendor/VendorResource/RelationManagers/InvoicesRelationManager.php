<?php

namespace App\Filament\Resources\Vendor\VendorResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Accounting\Invoice;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class InvoicesRelationManager extends RelationManager {
    protected static string $relationship = 'invoices';

    public function form(Form $form): Form {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        Select::make('building_id')
                            ->relationship('building', 'name')
                            ->preload()
                            ->disabled()
                            ->searchable()
                            ->label('Building Name'),
                        Select::make('contract_id')
                            ->relationship('contract', 'contract_type')
                            ->preload()
                            ->disabled()
                            ->searchable()
                            ->label('Contract Type'),
                        TextInput::make('invoice_number')
                            ->required()
                            ->disabled()
                            ->maxLength(255),
                        Select::make('wda_id')
                            ->relationship('wda', 'job_description')
                            ->preload()
                            ->disabled()
                            ->searchable()
                            ->label('Job Description(WDA)'),
                        DatePicker::make('date')
                            ->rules(['date'])
                            ->required()
                            ->disabled()
                            ->label('Start Date'),
                        FileUpload::make('document')
                            ->disk('s3')
                            ->directory('dev')
                            ->disabled()
                            ->openable(true)
                            ->downloadable(true)
                            ->label('Document'),
                        TextInput::make('invoice_amount')
                            ->label('Invoice Amount')
                            ->disabled()
                            ->prefix('AED'),
                        Select::make('status')
                            ->options([
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->disabled(function (Invoice $record) {
                                return $record->status != 'pending';
                            })
                            ->searchable()
                            ->live(),
                        TextInput::make('remarks')
                            ->rules(['max:255'])
                            ->visible(function (callable $get) {
                                if($get('status') == 'rejected') {
                                    return true;
                                }
                                return false;
                            })
                            ->disabled(function (Invoice $record) {
                                return $record->status != 'pending';
                            })
                            ->required(),
                    ])
            ]);
    }

    public function table(Table $table): Table {
        return $table
            ->columns([
                TextColumn::make('building.name')
                    ->label('Building'),
                TextColumn::make('contract.contract_type')
                    ->label('Contract Type'),
                TextColumn::make('invoice_number')
                    ->label('Invoice Number'),
                TextColumn::make('wda.job_description')
                    ->label('Job Description(WDA)'),
                TextColumn::make('date')
                    ->default('NA')
                    ->label('Start Date'),
                TextColumn::make('status')
                    ->default('NA')
                    ->label('Status'),
                TextColumn::make('invoice_amount')
                    ->default('NA')
                    ->label('Invoice Amount'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if($data['status'] != 'pending'){
                            $data['status_updated_by'] = auth()->user()->id;
                        }
                        return $data;
                    })
                    ->mutateRecordDataUsing(function (array $data): array {
                        if($data['status'] = 'pending'){
                            $data['status'] = null;
                        }
                        return $data;
                    })
                //Tables\Actions\DeleteAction::make(),
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
}
