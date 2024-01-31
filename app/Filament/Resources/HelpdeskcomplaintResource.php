<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\Vendor\Vendor;
use App\Models\TechnicianVendor;
use Filament\Resources\Resource;
use App\Models\Building\Complaint;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use App\Models\Vendor\ServiceVendor;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\HelpdeskcomplaintResource\Pages;

class HelpdeskcomplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Complaint';

    protected static ?string $navigationGroup = 'Help Desk';

    public static function form(Form $form): Form
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
                            ->required(function(Get $get){
                                if($get('category')=='Security Services'){
                                    return false;
                                }
                                return true;
                            })
                            ->options(function (Complaint $record, Get $get) {
                                $serviceVendor = ServiceVendor::where('service_id', $get('service_id'))->pluck('vendor_id');
                                return Vendor::whereIn('id', $serviceVendor)->where('owner_association_id', auth()->user()->owner_association_id)->pluck('name', 'id');
                            })
                            ->disabled(function (Complaint $record) {
                                if ($record->category=='Security Services') {
                                    return true;
                                }
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
                            ->required()
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
                            ->rules([
                                function () {
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
                            ->searchable()
                            ->label('Service'),
                        TextInput::make('category')->disabled(),
                        TextInput::make('open_time')->disabled(),
                        TextInput::make('close_time')->disabled()->default('NA'),
                        TextInput::make('complaint')
                            ->disabled()
                            ->placeholder('Complaint'),
                        Textarea::make('complaint_details')
                            ->disabled()
                            ->placeholder('Complaint Details'),
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
                            ->rules(['max:255'])
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
    public static function table(Table $table): Table
    {
        return $table
            // ->modifyQueryUsing(fn(Builder $query) => $query->where('complaintable_type', 'App\Models\Building\Building')->withoutGlobalScopes())
            // ->poll('60s')
            ->columns([
                // ViewColumn::make('name')->view('tables.columns.combined-column')
                //     ->toggleable(),
                TextColumn::make('building.name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('user.first_name')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
                TextColumn::make('category')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
                TextColumn::make('complaint')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('status')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),


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
                    ->label('Building')
                    ->preload()
            ])
            ->actions([]);
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
            'index' => Pages\ListHelpdeskcomplaints::route('/'),
            // 'view' => Pages\ViewHelpdeskcomplaint::route('/{record}'),
            'edit' => Pages\EditHelpdeskcomplaint::route('/{record}/edit'),
        ];
    }
}
