<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NocFormResource\Pages;
use App\Filament\Resources\NocFormResource\RelationManagers;
use App\Models\Forms\NocForms;
use App\Models\Forms\SaleNOC;
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
    protected static ?string $model = SaleNOC::class;
    protected static ?string $modelLabel = 'Sale NOC';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('unit_occupied_by'),
                TextInput::make('applicant'),
                TextInput::make('unit_area'),
                TextInput::make('sale_price'),
                TextInput::make('status'),
                TextInput::make('remarks'),
                Select::make('user_id')
                        ->rules(['exists:users,id'])
                        ->relationship('user', 'first_name')
                        ->required()
                        ->preload()
                        ->searchable()
                        ->label('User'),
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
                FileUpload::make('cooling_receipt')
                        ->disk('s3')
                        ->directory('dev')
                        ->label('Cooling Receipt')
                        ->downloadable()
                        ->openable()
                        ->columnSpan([
                            'sm'=> '1',
                            'md'=> '1',
                            'lg'=> '2',
                        ]),
                FileUpload::make('cooling_soa')
                        ->disk('s3')
                        ->directory('dev')
                        ->label('Cooling Soa')
                        ->downloadable()
                        ->openable()
                        ->columnSpan([
                            'sm'=> '1',
                            'md'=> '1',
                            'lg'=> '2',
                        ]),
                FileUpload::make('cooling_clearance')
                        ->disk('s3')
                        ->directory('dev')
                        ->label('Cooling Clearance')
                        ->downloadable()
                        ->openable()
                        ->columnSpan([
                            'sm'=> '1',
                            'md'=> '1',
                            'lg'=> '2',
                        ]),
                FileUpload::make('payment_receipt')
                        ->disk('s3')
                        ->directory('dev')
                        ->label('Payment Receipt')
                        ->downloadable()
                        ->openable()
                        ->columnSpan([
                            'sm'=> '1',
                            'md'=> '1',
                            'lg'=> '2',
                        ]),
                Toggle::make('cooling_bill_paid')
                ->columnSpan([
                    'sm'=> '1',
                    'md'=> '1',
                    'lg'=> '2',
                ]),
                Toggle::make('service_charge_paid')
                ->columnSpan([
                    'sm'=> '1',
                    'md'=> '1',
                    'lg'=> '2',
                ]),
                Toggle::make('noc_fee_paid')
                ->columnSpan([
                    'sm'=> '1',
                    'md'=> '1',
                    'lg'=> '2',
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.first_name')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->default('NA'),
                ImageColumn::make('cooling_receipt')
                    ->square()
                    ->alignCenter()
                    ->disk('s3'),
                ImageColumn::make('cooling_soa')
                    ->square()
                    ->alignCenter()
                    ->disk('s3'),
                ImageColumn::make('cooling_clearance')
                    ->square()
                    ->alignCenter()
                    ->disk('s3'),
                ImageColumn::make('payment_receipt')
                    ->square()
                    ->alignCenter()
                    ->disk('s3'),
                TextColumn::make('status')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('remarks')
                    ->searchable()
                    ->default('NA'),
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
