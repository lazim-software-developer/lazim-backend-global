<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use Closure;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Vendor\Vendor;
use App\Models\TechnicianVendor;
use App\Models\Building\Complaint;
use App\Models\Vendor\ServiceVendor;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ComplaintRelationManager extends RelationManager
{
    protected static string $relationship = 'complaint';
    protected static ?string $modelLabel = 'Snag';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Snags';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2
                ])
                    ->schema([
                        Select::make('building_id')
                            ->rules(['exists:buildings,id'])
                            ->relationship('building', 'name')
                            ->reactive()
                            ->disabled()
                            ->preload()
                            ->searchable()
                            ->placeholder('Building'),
                        Select::make('user_id')
                            ->relationship('user', 'first_name')
                            ->options(function () {
                                $tenants = DB::table('flat_tenants')->pluck('tenant_id');
                                // dd($tenants);
                                return DB::table('users')
                                    ->whereIn('users.id', $tenants)
                                    ->select('users.id', 'users.first_name')
                                    ->pluck('users.first_name', 'users.id')
                                    ->toArray();
                            })
                            ->disabled()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('User'),
                        Select::make('vendor_id')
                            ->relationship('vendor', 'name')
                            ->preload()
                            ->required()
                            ->options(function (Complaint $record, Get $get) {
                                $serviceVendor = ServiceVendor::where('service_id',$get('service_id'))->pluck('vendor_id');
                                return Vendor::whereIn('id',$serviceVendor)->where('owner_association_id', auth()->user()->owner_association_id)->pluck('name', 'id');
                            })
                            ->disabled(function (Complaint $record) {
                                if ($record->vendor_id == null) {
                                    return false;
                                }
                                return true;

                            })
                            ->live()
                            ->searchable()
                            ->label('vendor Name'),
                        Select::make('flat_id')
                            ->rules(['exists:flats,id'])
                            ->disabled()
                            ->relationship('flat', 'property_number')
                            ->searchable()
                            ->preload()
                            ->placeholder('Unit Number'),
                        Select::make('technician_id')
                            ->relationship('technician', 'first_name')
                            ->options(function (Complaint $record, Get $get) {
                                $technician_vendor = DB::table('service_technician_vendor')->where('service_id', $record->service_id)->pluck('technician_vendor_id');
                                $technicians = TechnicianVendor::find($technician_vendor)->where('vendor_id', $get('vendor_id'))->pluck('technician_id');
                                return User::find($technicians)->pluck('first_name', 'id');
                            })
                            ->disabled(function (Complaint $record) {
                                return $record->status != 'open';
                            })
                            ->preload()
                            ->searchable()
                            ->label('Technician Name'),
                        TextInput::make('priority')
                            ->rules([function () {
                                return function (string $attribute, $value, Closure $fail) {
                                    if ($value < 1 || $value > 3) {
                                        $fail('The priority field accepts 1, 2 and 3 only.');
                                    }
                                };
                            },
                            ])
                            ->disabled(function (Complaint $record) {
                                return $record->status != 'open';
                            })
                            ->numeric(),
                        DatePicker::make('due_date')
                            ->minDate(now()->format('Y-m-d'))
                            ->rules(['date'])
                            ->disabled(function (Complaint $record) {
                                return $record->status != 'open';
                            })
                            ->placeholder('Due Date'),
                        Repeater::make('media')
                            ->relationship()
                            ->disabled()
                            ->schema([
                                FileUpload::make('url')
                                    ->disk('s3')
                                    ->directory('dev')
                                    ->maxSize(2048)
                                    ->openable(true)
                                    ->downloadable(true)
                                    ->label('File'),
                            ])
                            ->columnSpan([
                                'sm' => 1,
                                'md' => 1,
                                'lg' => 2
                            ]),
                        Select::make('service_id')
                            ->relationship('service', 'name')
                            ->preload()
                            ->disabled()
                            ->live()
                            ->searchable()
                            ->label('Service'),
                        TextInput::make('category')->disabled(),
                        TextInput::make('open_time')->disabled(),
                        TextInput::make('close_time')->disabled()->default('NA'),
                        Textarea::make('complaint')
                            ->disabled()
                            ->placeholder('Complaint'),
                        // Textarea::make('complaint_details')
                        //     ->disabled()
                        //     ->placeholder('Complaint Details'),
                        Select::make('status')
                            ->options([
                                'open' => 'Open',
                                'closed' => 'Closed',
                            ])
                            ->disabled(function (Complaint $record) {
                                return $record->status != 'open';
                            })
                            ->searchable()
                            ->live(),
                        TextInput::make('remarks')
                            ->rules(['max:150'])
                            ->visible(function (callable $get) {
                                if ($get('status') == 'closed') {
                                    return true;
                                }
                                return false;
                            })
                            ->disabled(function (Complaint $record) {
                                return $record->status != 'open';
                            })
                            ->required(),

                    ])
            ]);

    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('complaint_type', ['snag']))
            ->columns([
                TextColumn::make('building.name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('user.first_name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                 TextColumn::make('complaint')
                    ->toggleable()
                    ->default('NA')
                    ->limit(20)
                    ->searchable()
                    ->label('Complaint'),
                // TextColumn::make('complaint_details')
                //     ->toggleable()
                //     ->default('NA')
                //     ->searchable()
                //     ->label('Complaint Details'),
                TextColumn::make('status')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
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
}
