<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\Vendor\Vendor;
use App\Models\BuildingVendor;
use App\Models\TechnicianVendor;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use App\Models\Building\Complaint;
use App\Models\OacomplaintReports;
use App\Models\Building\BuildingPoc;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use App\Filament\Resources\OacomplaintReportsResource\Pages;
use App\Models\Accounting\SubCategory;
use App\Models\Master\Service;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\DB;

class OacomplaintReportsResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $modelLabel = 'OA Complaint Report';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Grid::make([
                'sm' => 1,
                'md' => 2,
                'lg' => 2   ,
            ])->schema([
                TextInput::make('ticket_number')
                    ->disabled()
                    ->visible(fn($livewire) => $livewire instanceof Pages\EditOacomplaintReports) // Only show on edit page
                    ->label('Ticket Number'),
                Select::make('type')
                    ->options([
                        'Technician' => 'Technician',
                        'Vendor'     => 'Vendor',
                        'Gatekeeper' => 'Gatekeeper',
                    ])
                    ->afterStateUpdated(function (Set $set) {
                        $set('user_id', null);
                    })
                    ->searchable()
                    ->live()->disabledOn('edit')
                    ->required(),

                Select::make('building_id')
                    ->relationship('building', 'name')
                    ->preload()->disabledOn('edit')
                    ->required()
                    ->live()
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                            return Building::where('owner_association_id', auth()->user()?->owner_association_id)
                                ->pluck('name', 'id');
                        }
                        return Building::whereNotNull('name')->pluck('name', 'id');
                    })
                    ->afterStateUpdated(function (Set $set) {
                        $set('user_id', null);
                    })
                    ->live()
                    ->searchable(),

                Select::make('Category')
                    ->preload()->required()
                    ->searchable()->live()->hiddenOn('edit')
                    ->options(function () {
                        return SubCategory::whereNot('name','Security Services')->pluck('name', 'id');
                    })
                    ->afterStateUpdated(
                        function (Set $set) {
                            $set('service_id', null);
                        })->visible(function (Get $get) {
                        if ($get('type') == 'Technician' || $get('type') == 'Vendor') {
                            return true;
                        }
                        return false;
                    }),
                Select::make('service_id')
                    ->searchable()
                    ->required()->preload()
                    ->relationship('service','name')
                    ->label('Service')->disabledOn('edit')
                    ->getSearchResultsUsing(fn (string $search,callable $get): array => Service::where('subcategory_id', $get('Category'))->where('type', 'vendor_service')->where('active',true)->where('name', 'like', "%{$search}%")->pluck('name', 'id')->toArray())
                    ->options(
                        function (Get $get) {
                            return Service::where('subcategory_id', $get('Category'))->where('type', 'vendor_service')->where('active',true)->pluck('name','id')->toArray();
                        }
                    )->visible(function (Get $get) {
                        if ($get('type') == 'Technician' || $get('type') == 'Vendor') {
                            return true;
                        }
                        return false;
                    }),
                Select::make('user_id')->label('User')->disabledOn('edit')
                    // ->relationship('user', 'first_name')
                    ->options(function (Get $get) {
                        if ($get('type') === 'Technician') {
                            $buildingVendor   = BuildingVendor::where('building_id', $get('building_id'))->where('active', 1)->pluck('vendor_id');
                            $technicianVendor = TechnicianVendor::whereIn('vendor_id', $buildingVendor)->pluck('technician_id');
                            return User::whereIn('id', $technicianVendor)->pluck('first_name', 'id');
                        }
                        if ($get('type') === 'Vendor') {
                            $buildingVendor = BuildingVendor::where('building_id', $get('building_id'))->where('active', 1)->pluck('vendor_id');
                            return Vendor::whereIn('id', $buildingVendor)->whereHas('ownerAssociation', function ($query) {
                                $query->where('status', 'approved');
                            })->pluck('name', 'id');
                            // return User::whereIn('id', $Vendors)->pluck('first_name', 'id');
                        }
                        if ($get('type') === 'Gatekeeper') {
                            $user = BuildingPoc::where('building_id', $get('building_id'))->where('active', 1)->pluck('user_id');
                            return User::whereIn('id', $user)->pluck('first_name', 'id');
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->dehydrateStateUsing(function ($state, $get, $set) {
                        if ($get('type') === 'Technician') {
                            $set('technician_id', $state);
                            return null;
                        } elseif ($get('type') === 'Vendor') {
                            $set('vendor_id', $state);
                            return null;
                        } elseif ($get('type') === 'Gatekeeper') {
                            return $state;
                        }
                    })
                    ->live()
                    ->required(),

                Textarea::make('complaint')->label('Issue')
                    ->maxLength(350)
                    ->required()->disabledOn('edit'),
                // FileUpload::make('image')
                //     ->disk('s3')
                //     ->rules('file|mimes:jpeg,jpg,png|max:2048')
                //     ->directory('dev')
                //     ->openable(true)
                //     ->downloadable(true)
                //     ->image()
                //     ->maxSize(2048)
                //     ->required()
                //     ->label('Image')
                //     ->columnSpanFull(),
                Repeater::make('media')->label('')
                    ->relationship()
                    ->schema([
                        FileUpload::make('url')
                            ->disk('s3')
                            ->rules('file|mimes:jpeg,jpg,png|max:2048')
                            ->directory('dev')
                            ->openable(true)
                            ->downloadable(true)
                            ->image()
                            ->required()
                            ->maxSize(2048)
                            ->label('Image')
                    ])
                    ->addable(false)->disabledOn('edit')
                    ->deletable(false),

                Hidden::make('complaintable_type')
                    ->default('App\Models\User\User'),

                Hidden::make('complaintable_id')
                    ->default(auth()->user()->id),

                Hidden::make('technician_id'),
                Hidden::make('vendor_id'),
                Hidden::make('owner_association_id')
                    ->default(auth()->user()->owner_association_id),
                Select::make('status')
                    ->options([
                        'open' => 'Open',
                        'closed' => 'Closed'
                    ])->hiddenOn('create')
                    ->disabled(function (?Complaint $record) {
                        return $record?->status == 'closed';
                    }),
                Hidden::make('category')
                    ->default('OA Complaint'),
                Hidden::make('complaint_type')
                    ->default('oa_complaint_report')
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_number')->searchable()->default("NA"),
                TextColumn::make('type')->searchable(),
                TextColumn::make('building.name')->searchable(),
                // TextColumn::make('user.first_name')->searchable(),
                // TextColumn::make('issue')->searchable()->limit(20),
                // ImageColumn::make('image')->disk('s3'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                    SelectFilter::make('building_id')
                        ->options(function () {
                            if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                return Building::all()->pluck('name', 'id');
                            } else {
                                $buildingId = DB::table('building_owner_association')->where('owner_association_id',auth()->user()?->owner_association_id)->where('active',true)->pluck('building_id');
                                return Building::whereIn('id',$buildingId)->pluck('name', 'id');
                            }
                        })
                        ->searchable()
                        ->preload()
                        ->label('Building')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
        // ->bulkActions([
        //     Tables\Actions\BulkActionGroup::make([
        //         Tables\Actions\DeleteBulkAction::make(),
        //     ]),
        // ]);
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
            'index'  => Pages\ListOacomplaintReports::route('/'),
            'create' => Pages\CreateOacomplaintReports::route('/create'),
            'edit'   => Pages\EditOacomplaintReports::route('/{record}/edit'),
        ];
    }
}
