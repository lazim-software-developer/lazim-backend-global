<?php

namespace App\Filament\Resources\Vendor\VendorResource\RelationManagers;

use App\Models\Accounting\WDA;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class WdasRelationManager extends RelationManager {
    protected static string $relationship = 'wdas';
    protected static ?string $modelLabel = 'WDA';

    public static function getTitle(Model $ownerRecord, string $pageClass): string {
        return 'WDA';
    }

    public function form(Form $form): Form {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        TextInput::make('wda_number')
                            ->disabled(),
                        DatePicker::make('date')
                            ->rules(['date'])
                            ->required()
                            ->disabled()
                            ->placeholder('Date'),
                        TextInput::make('job_description')
                            ->required()
                            ->disabled()
                            ->maxLength(255),
                        FileUpload::make('document')
                            ->disk('s3')
                            ->disabled()
                            ->directory('dev')
                            ->openable(true)
                            ->downloadable(true)
                            ->label('Document'),
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
                        Select::make('service_id')
                            ->relationship('service', 'name')
                            ->preload()
                            ->disabled()
                            ->searchable()
                            ->label('Service'),
                        Select::make('vendor_id')
                            ->relationship('vendor', 'name')
                            ->preload()
                            ->disabled()
                            ->disabled()
                            ->searchable()
                            ->label('vendor Name'),
                        Select::make('status')
                            ->options([
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->disabled(function (WDA $record) {
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
                            ->disabled(function (WDA $record) {
                                return $record->status != 'pending';
                            })
                            ->required(),
                    ])
            ]);
    }
    public function table(Table $table): Table {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->default('NA')
                    ->label('Date'),
                TextColumn::make('service.name')
                    ->default('NA')
                    ->label('Service'),
                TextColumn::make('status')
                    ->default('NA')
                    ->label('Status'),
                TextColumn::make('building.name')
                    ->label('Building'),
                TextColumn::make('contract.contract_type')
                    ->label('Contract Type'),
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
                        if($data['status'] != 'pending') {
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
