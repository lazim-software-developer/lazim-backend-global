<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\Accounting\OAMInvoice;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OAMInvoiceResource\Pages;
use App\Filament\Resources\OAMInvoiceResource\RelationManagers;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\BulkAction;

class OAMInvoiceResource extends Resource
{
    protected static ?string $model = OAMInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'OAM Invoice';
    protected static ?string $navigationGroup = 'oam';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                    ->label('Property No'),
                TextInput::make('invoice_number')->disabled()->label('Invoice Number'),
                DatePicker::make('invoice_date')->disabled()->label('Invoice Date'),
                Select::make('invoice_status')->options(['Paid'=>'Paid','Defered'=>'Defered'])->searchable()->label('Invoice Status'),
                TextInput::make('due_amount')->label('Due Amount'),
                TextInput::make('general_fund_amount')->disabled()->label('General Fund Amount'),
                TextInput::make('reserve_fund_amount')->disabled()->label('Reserve Fund Amount'),
                TextInput::make('additional_charges')->disabled()->label('Additional Charges'),
                TextInput::make('previous_balance')->disabled()->label('Previous Balance'),
                TextInput::make('Adjust_amount')->disabled()->label('Adjust Amount'),
                TextInput::make('invoice_due_date')->disabled()->label('Invoice Due Date'),
                TextInput::make('invoice_pdf_link')
                    ->label('Invoice Pdf Link'),
                TextInput::make('invoice_detail_link')
                    ->label('Invoice Detail Link'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('invoice_number')
                    ->searchable()
                    ->default("NA")
                    ->label('Invoice Number'),
                TextColumn::make('invoice_date')
                    ->date(),
                TextColumn::make('invoice_status')
                    ->searchable()
                    ->default("NA")
                    ->label('Invoice Status'),
                TextColumn::make('due_amount')
                    ->searchable()
                    ->default("NA")
                    ->label('Due Amount'),
                TextColumn::make('')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('Remind')
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
                            ->required()
                            ->visible(function (callable $get) {
                                if ($get('status') == 'rejected') {
                                    return true;
                                }
                                return false;
                            }),
                        ])
                        ->fillForm(fn (OAMInvoice $record): array => [
                            'status' => $record->status,
                            'remarks' => $record->remarks,
                        ])
                        ->action(function (OAMInvoice $record, array $data): void {
                            
                        })
                        ->slideOver()
                ]),
            ])
            ->emptyStateActions([
                //Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListOAMInvoices::route('/'),
            //'create' => Pages\CreateOAMInvoice::route('/create'),
            'edit' => Pages\EditOAMInvoice::route('/{record}/edit'),
        ];
    }
}
