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

class WdasRelationManager extends RelationManager
{
    protected static string $relationship = 'wdas';
    protected static ?string $modelLabel = 'WDA';

    public static function getTitle(Model $ownerRecord, string $pageClass): string    
    {         
        return 'WDA';     
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        DatePicker::make('date')
                            ->rules(['date'])
                            ->required()
                            ->placeholder('Date'),
                        TextInput::make('job_description')
                            ->required()
                            ->maxLength(255),
                        FileUpload::make('document')
                            ->disk('s3')
                            ->directory('dev')
                            ->openable(true)
                            ->downloadable(true)
                            ->label('Document'),
                        TextInput::make('status')
                            ->label('Status'),
                        TextInput::make('remarks')
                            ->label('Status'),
                        Select::make('building_id')
                            ->relationship('building', 'name')
                            ->preload()
                            ->searchable()
                            ->label('Building Name'),
                        Select::make('contract_id')
                            ->relationship('contract', 'contract_type')
                            ->preload()
                            ->searchable()
                            ->label('Contract Type'),
                        Select::make('status_updated_by')
                            ->rules(['exists:users,id'])
                            ->relationship('user', 'first_name')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->label('Status Updated By'),
                    ])
            ]);
    }
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->default('NA')
                    ->label('Date'),
                TextColumn::make('job_description')
                    ->default('NA')
                    ->label('Job Description'),
                ImageColumn::make('document')
                    ->square()
                    ->disk('s3')
                    ->label('Document'),
                TextColumn::make('status')
                    ->default('NA')
                    ->label('Status'),
                TextColumn::make('remarks')
                    ->default('NA')
                    ->label('Remarks'),
                TextColumn::make('building.name')
                    ->label('Building'),
                TextColumn::make('contract.contract_type')
                    ->label('Contract Type'),
                TextColumn::make('user.first_name')
                    ->label('Status Updated By')
                    ->default('NA'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('Update Status')
                ->visible(fn ($record) => $record->status === 'pending')
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
                        })
                        ->required(),
                ])
                ->fillForm(fn (WDA $record): array => [
                    'status' => $record->status,
                    'remarks' => $record->remarks,
                ])
                ->action(function (WDA $record, array $data): void {
                    if ($data['status'] == 'rejected') {
                        $record->status = $data['status'];
                        $record->remarks = $data['remarks'];
                        $record->status_updated_by = auth()->user()->id;
                        $record->save();
                    } else {
                        $record->status = $data['status'];
                        $record->status_updated_by = auth()->user()->id;
                        $record->save();
                    }
                })
                ->slideOver()
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
