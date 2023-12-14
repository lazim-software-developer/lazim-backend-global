<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Vendor\Vendor;
use App\Models\TechnicianVendor;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use App\Models\Building\Complaint;
use Illuminate\Support\Facades\DB;
use App\Models\Building\FlatTenant;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\HelpdeskcomplaintResource\Pages;
use App\Filament\Resources\HelpdeskcomplaintResource\RelationManagers;

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
                            ->relationship('user', 'id')
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
                            ->options(function (Complaint $record) {
                                return Vendor::where('owner_association_id', auth()->user()->owner_association_id)->pluck('name', 'id');
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
                            ->preload()
                            ->searchable()
                            ->label('Technician Name'),
                        TextInput::make('priority')
                            ->rules(['regex:/^[1-3]$/'])
                            ->numeric(),
                        DatePicker::make('due_date')
                            ->minDate(now())
                            ->rules(['date'])
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
                        TextInput::make('complaint_details')
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
                    ->relationship('building', 'name')
                    ->searchable()
                    ->label('Building')
                    ->preload()
            ])
            ->actions([

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
            'index' => Pages\ListHelpdeskcomplaints::route('/'),
            // 'view' => Pages\ViewHelpdeskcomplaint::route('/{record}'),
            'edit' => Pages\EditHelpdeskcomplaint::route('/{record}/edit'),
        ];
    }
}
