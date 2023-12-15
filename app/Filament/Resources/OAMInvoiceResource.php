<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\FlatOwners;
use Filament\Tables\Table;
use App\Models\ApartmentOwner;
use Filament\Resources\Resource;
use App\Jobs\OAM\InvoiceDueMailJob;
use App\Models\Accounting\OAMInvoice;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OAMInvoiceResource\Pages;
use App\Filament\Resources\OAMInvoiceResource\RelationManagers;

class OAMInvoiceResource extends Resource
{
    protected static ?string $model = OAMInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Delinquent Owners';
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
                    ->label('Unit Number'),
                TextInput::make('invoice_number')->disabled()->label('Invoice Number'),
                DatePicker::make('invoice_date')->disabled()->label('Invoice Date'),
                Select::make('invoice_status')->options(['Paid' => 'Paid', 'Defered' => 'Defered'])->searchable()->label('Invoice Status'),
                TextInput::make('due_amount')->label('Due Amount')->prefix('AED'),
                TextInput::make('general_fund_amount')->disabled()->label('General Fund Amount')->prefix('AED'),
                TextInput::make('reserve_fund_amount')->disabled()->label('Reserve Fund Amount')->prefix('AED'),
                TextInput::make('additional_charges')->disabled()->label('Additional Charges')->prefix('AED'),
                TextInput::make('previous_balance')->disabled()->label('Previous Balance')->prefix('AED'),
                TextInput::make('Adjust_amount')->disabled()->label('Adjust Amount')->prefix('AED'),
                TextInput::make('invoice_due_date')->disabled()->label('Invoice Due Date'),

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
                    ->label('Unit Number')
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
                TextColumn::make('invoice_due_date')
                    ->date(),
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
                            Textarea::make('content')
                                ->maxLength(1024)
                                ->rows(10)
                                ->label('Content'),
                        ])
                        ->fillForm(fn(OAMInvoice $record): array => [
                            'content' => 'Your payment is Due, please make the payment ASAP.'
                        ])
                        ->action(function (Collection $records, array $data): void {
                            foreach ($records as $record) {
                                // Access the flat_id of each selected record
                                $flatId = $record->flat_id;

                                $ownerID = FlatOwners::where('flat_id', $flatId)->where('active', true)->first()->owner_id;
                                $owner = ApartmentOwner::find($ownerID);
                                $content = $data['content'];
                                InvoiceDueMailJob::dispatch($owner, $content);
                            }
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
