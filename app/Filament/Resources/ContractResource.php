<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\Vendor\Vendor;
use App\Models\Master\Service;
use App\Models\Vendor\Contract;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ContractResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ContractResource\RelationManagers;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Oam';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        Select::make('contract_type')
                            ->options([
                                'annual maintenance contract' => 'Annual Maintenance Contract',
                                'onetime' => 'OneTime',
                            ])
                            ->disabledOn('edit')
                            ->searchable()
                            ->required()
                            ->label('Contract Type'),
                        Select::make('building_id')
                            ->relationship('building', 'name')
                            ->options(function(){
                                return Building::where('owner_association_id',auth()->user()->owner_association_id)->pluck('name','id');
                            })
                            ->reactive()
                            ->required()
                            ->preload()
                            ->disabledOn('edit')
                            ->searchable()
                            ->placeholder('Building'),
                        Select::make('service_id')
                            ->relationship('service', 'name')
                            ->options(function(){
                                return Service::where('type','vendor_service')->pluck('name','id');
                            })
                            ->reactive()
                            ->required()
                            ->preload()
                            ->searchable()
                            ->disabledOn('edit')
                            ->placeholder('Service'),
                        DatePicker::make('start_date')
                            ->required()
                            ->rules(['date'])
                            ->minDate(now()->format('Y-m-d'))
                            ->disabledOn('edit')
                            ->placeholder('Start Date'),
                        DatePicker::make('end_date')
                            ->required()
                            ->rules(['date'])
                            ->minDate(now()->format('Y-m-d'))
                            ->disabledOn('edit')
                            ->placeholder('End Date'),
                        FileUpload::make('document_url')
                            ->required()
                            ->acceptedFileTypes(['application/pdf'])
                            ->disk('s3')
                            ->directory('dev')
                            ->openable(true)
                            ->downloadable(true)
                            ->label('Document'),
                        TextInput::make('amount')
                            ->numeric(true)
                            ->minValue(1)
                            ->prefix('AED')
                            ->required(),
                        TextInput::make('budget_amount')
                            ->numeric(true)
                            ->minValue(1)
                            ->prefix('AED')
                            ->required(),
                        Select::make('vendor_id')
                            ->relationship('vendor', 'name')
                            ->options(function(){
                                return Vendor::where('owner_association_id',auth()->user()->owner_association_id)->pluck('name','id');
                            })
                            ->reactive()
                            ->required()
                            ->preload()
                            ->searchable()
                            ->disabledOn('edit')
                            ->placeholder('Vendor'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contract_type')->label('Contract Type')->searchable(),
                Tables\Columns\TextColumn::make('start_date')->label('Start Date'),
                Tables\Columns\TextColumn::make('end_date')->label('End Date'),
                Tables\Columns\TextColumn::make('amount')->label('Amount'),
                Tables\Columns\TextColumn::make('budget_amount')->label('Budget Amount'),
                // Tables\Columns\ImageColumn::make('document_url')->square()->disk('s3')->label('Document'),
                Tables\Columns\TextColumn::make('building.name')->label('Building')->searchable(),
                Tables\Columns\TextColumn::make('service.name')->label('Service')->searchable(),
                Tables\Columns\TextColumn::make('vendor.name')->label('Vendor')->searchable(),
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
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
        ];
    }
}
