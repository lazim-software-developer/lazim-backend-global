<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\Vendor\Vendor;
use App\Models\Master\Service;
use Filament\Facades\Filament;
use App\Models\Vendor\Contract;
use Filament\Resources\Resource;
use App\Models\Accounting\Budget;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use App\Models\Accounting\Budgetitem;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ContractResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\ContractResource\RelationManagers;
use Filament\Forms\Components\Section;
use Illuminate\Support\Facades\Log;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Oam';

    public static $bm = null;

    public static function form(Form $form): Form
    {
        return $form
    ->schema([
        Section::make('Contract Information')
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([
                    Select::make('contract_type')
                        ->options([
                            'AMC' => 'AMC',
                            'One time' => 'One Time',
                        ])
                        ->disabledOn('edit')
                        ->searchable()
                        ->required()
                        ->label('Contract Type'),
                    Select::make('building_id')
                        ->relationship('building', 'name')
                        ->options(function(){
                            if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
                                return Building::all()->pluck('name', 'id');
                            }
                            else{
                                return Building::where('owner_association_id', auth()->user()?->owner_association_id)
                                    ->pluck('name', 'id');
                            } 
                        })
                        ->reactive()
                        ->required()
                        ->preload()
                        ->disabledOn('edit')
                        ->searchable()
                        ->placeholder('Building')
                        ->live(),
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
                        ->placeholder('Service')
                        ->live(),
                    Select::make('vendor_id')
                        ->relationship('vendor', 'name')
                        ->options(function(){
                            if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
                                return Vendor::where('status','approved')->pluck('name','id');
                            }else{
                                return Vendor::whereHas('ownerAssociation', function ($query) {
                                    $query->where('owner_association_id', Filament::getTenant()->id)
                                        ->where('status', 'approved');
                                })->pluck('name','id');
                            }
                        })
                        ->reactive()
                        ->required()
                        ->preload()
                        ->searchable()
                        ->disabledOn('edit')
                        ->placeholder('Vendor')
                        ->live(),
                    DatePicker::make('start_date')
                        ->disabledOn('edit')
                        ->required()
                        ->rules(['date'])
                        ->placeholder('Start Date')
                        ->live(),
                    DatePicker::make('end_date')
                        ->afterOrEqual('start_date')
                        ->required()
                        ->rules(['date'])
                        ->placeholder('End Date'),
                ]),
            ]),

        Section::make('Budget Information')
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([
                    TextInput::make('budget_amount')
                        ->numeric(true)
                        ->live()
                        ->disabledOn('edit')
                        ->minValue(1)
                        ->maxValue(1000000)
                        ->prefix('AED')
                        ->required()
                        ->reactive()
                        ->readOnly(function (callable $set, callable $get) {
                            $buildingId = $get('building_id');
                            $startDate = $get('start_date');
                            $serviceId = $get('service_id');
                            $vendor_id = $get('vendor_id');
                    
                            if (!$buildingId || !$startDate || !$serviceId || !$vendor_id) {
                                return false;
                            }
                    
                            $startYear = Carbon::parse($startDate)->year;
                            
                            $budgetId = Budget::where('building_id', $buildingId)
                                ->where('owner_association_id', auth()->user()->owner_association_id)
                                ->whereYear('budget_from', $startYear)->value('id') ?? null;
                    
                            $budget = Budgetitem::where('budget_id', $budgetId)
                                ->where('service_id', $serviceId)
                                ->value('total') ?? null;

                            $contractsQuery = Contract::where([
                                ['building_id', $get('building_id')],
                                ['service_id', $get('service_id')],
                                ['vendor_id', $get('vendor_id')]
                            ]);
                            
                            $contractAmountSum = $contractsQuery->sum('amount');
                            
                            if ($contractAmountSum > 0) {
                                $budgetAmount = $get('budget_amount'); 
                                $difference = abs($budgetAmount - $contractAmountSum);
                                $difference = round($difference, 2);
                            
                                self::$bm = $difference; 
                            } else {
                                self::$bm = $budget;
                            }

                            $set('remaining_amount', self::$bm);
                            $set('budget_amount', $budget);
                                
                            return true;
                        }),

                    TextInput::make('remaining_amount')
                        ->label('Balance amount')
                        ->prefix('AED')
                        ->readOnly(),

                    TextInput::make('amount')
                        ->label('Contract amount')
                        ->numeric(true)
                        ->disabledOn('edit')
                        ->minValue(1)
                        ->maxValue(function(callable $get){
                            return $get('remaining_amount');
                        })
                        ->prefix('AED')
                        ->helperText('Contract Amount must be less than Balance Amount')
                        ->required(),
                ]),
            ]),

        Section::make('Documents')
            ->schema([
                FileUpload::make('document_url')
                    ->required()
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk('s3')
                    ->directory('dev')
                    ->openable(true)
                    ->downloadable(true)
                    ->label('Document'),
            ])
            ->columns(2),
    ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contract_type')->label('Contract type')->searchable(),
                Tables\Columns\TextColumn::make('start_date')->label('Start date'),
                Tables\Columns\TextColumn::make('end_date')->label('End date'),
                Tables\Columns\TextColumn::make('amount')->label('Amount'),
                Tables\Columns\TextColumn::make('budget_amount')->label('Budget amount'),
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
                            $query->where('owner_association_id', Filament::getTenant()?->id);
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
