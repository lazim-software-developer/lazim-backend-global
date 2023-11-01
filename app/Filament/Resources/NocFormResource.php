<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NocFormResource\Pages;
use App\Filament\Resources\NocFormResource\RelationManagers;
use App\Models\Forms\NocForms;
use App\Models\NocForm;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NocFormResource extends Resource
{
    protected static ?string $model = NocForms::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('unit_occupied_by'),
                TextInput::make('applicant'),
                TextInput::make('unit_area'),
                TextInput::make('sale_price'),
                Toggle::make('cooling_bill_paid'),
                Toggle::make('service_charge_paid'),
                Toggle::make('noc_fee_paid'),
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
                DatePicker::make('service_charge_paid_till')
                        ->date(),
                FileUpload::make('cooling_receipt_url')
                        ->disk('s3')
                        ->directory('dev'),
                FileUpload::make('cooling_soa_url')
                        ->disk('s3')
                        ->directory('dev'),
                FileUpload::make('cooling_clearance_url')
                        ->disk('s3')
                        ->directory('dev'),
                FileUpload::make('payment_receipt_url')
                        ->disk('s3')
                        ->directory('dev'),
                Repeater::make('contact')
                        ->relationship('contact')
                        ->schema([
                            TextInput::make('type'),
                            TextInput::make('first_name'),
                            TextInput::make('last_name'),
                            TextInput::make('email'),
                            TextInput::make('mobile'),
                            TextInput::make('emirates_id'),
                            TextInput::make('passport_number'),
                            TextInput::make('visa_number'),
                        ])
                        ->columnSpan([
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 2,
                        ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('unit_occupied_by')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('applicant')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('unit_area')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('sale_price')
                    ->searchable()
                    ->default('NA'),
                IconColumn::make('cooling_bill_paid')
                    ->boolean(),
                IconColumn::make('service_charge_paid')
                    ->boolean(),
                IconColumn::make('noc_fee_paid')
                    ->boolean(),
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('service_charge_paid_till')
                    ->date(),
                ImageColumn::make('cooling_receipt_url')
                    ->circular()
                    ->disk('s3'),
                ImageColumn::make('cooling_soa_url')
                    ->circular()
                    ->disk('s3'),
                ImageColumn::make('cooling_clearance_url')
                    ->circular()
                    ->disk('s3'),
                ImageColumn::make('payment_receipt_url')
                    ->circular()
                    ->disk('s3'),
            ])
            ->filters([
                SelectFilter::make('building_id')
                    ->relationship('building', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Building'),
                SelectFilter::make('flat_id')
                    ->relationship('flat', 'property_number')
                    ->searchable()
                    ->preload()
                    ->label('Flat Number'),
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
                Action::make('Update Status')
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
                            ->visible(function(callable $get){
                                if($get('status')=='rejected')
                                {
                                    return true;
                                }
                                return false;
                            }),
                    ])
                    ->fillForm(fn (NocForms $record): array => [
                        'status' => $record->status,
                        'remarks' => $record->remarks,
                    ])
                    ->action(function (NocForms $record,array $data): void {
                        if($data['status'] == 'rejected')
                        {
                            $record->status = $data['status'];
                            $record->remarks = $data['remarks'];
                            $record->save();
                        }
                        else
                        {
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
            'index' => Pages\ListNocForms::route('/'),
            'view' => Pages\ViewNocForm::route('/{record}'),
            //'create' => Pages\CreateNocForm::route('/create'),
            //'edit' => Pages\EditNocForm::route('/{record}/edit'),
        ];
    }    
}
