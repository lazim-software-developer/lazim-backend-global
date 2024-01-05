<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use Closure;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Asset;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Service;
use App\Models\Vendor\Contract;
use App\Forms\Components\QrCode;
use App\Models\TechnicianAssets;
use App\Models\TechnicianVendor;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use App\Models\Assets\Assetmaintenance;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class AssetsRelationManager extends RelationManager
{
    protected static string $relationship = 'assets';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        Hidden::make('building_id')
                            ->default(function (RelationManager $livewire) {
                                return $livewire->ownerRecord->id;
                            }),
                        TextInput::make('name')
                            ->rules([
                                'max:50',
                                'regex:/^[a-zA-Z\s]*$/',
                                fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                    if (Asset::where('building_id', $get('building_id'))->where('name', $value)->exists()) {
                                        $fail('The Name is already taken for this Building.');
                                    }
                                },
                            ])
                            ->required()
                            ->label('Asset Name'),
                        TextInput::make('location')
                            ->required()
                            ->rules(['max:50', 'regex:/^(?=.*[a-zA-Z])[a-zA-Z0-9\s!@#$%^&*_+\-=,.]*$/'])
                            ->label('Location'),
                        Textarea::make('description')
                            ->label('Description')
                            ->rules(['max:100', 'regex:/^(?=.*[a-zA-Z])[a-zA-Z0-9\s!@#$%^&*_+\-=,.]*$/']),
                        Select::make('service_id')
                            ->relationship('service', 'name')
                            ->options(function () {
                                return Service::where('type', 'vendor_service')->where('active', 1)->pluck('name', 'id');
                            })
                            ->required()
                            ->preload()
                            ->searchable()
                            ->label('Service'),
                    ]),
                QrCode::make('qr_code')
                    ->label('QR Code')
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 2,
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Asset Name'),
                TextColumn::make('location')->label('Location'),
                TextColumn::make('description')->label('Description'),
                TextColumn::make('service.name')->label('Service Name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function (Asset $record) {
                        // Fetch asset details from the database
                        $asset = Asset::where('id', $record->id)->first();
                        // Fetch technician_asset details
                        $technician_asset_id = TechnicianAssets::where('asset_id', $asset)->first();
                        // Fetch Building name
                        $building_name = Building::where('id', $asset->building_id)->first();
                        // Fetch maintenance details from the database
                        $maintenance = Assetmaintenance::where('technician_asset_id', $technician_asset_id)->first();

                        // Build an object with the required properties
                        $qrCodeContent = [
                            'id' => $record->id,
                            'technician_asset_id' => $technician_asset_id,
                            'asset_id' => $record->id,
                            'asset_name' => $asset->name,
                            'maintenance_status' => 'not-started',
                            'building_name' => $building_name->name,
                            'building_id' => $asset->building_id,
                            'location' => $asset->location,
                            'description' => $asset->description,
                            // 'last_service_on' => $maintenance->maintenance_date,
                        ];

                        // Generate a QR code using the QrCode library
                        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->generate(json_encode($qrCodeContent));

                        // Update the newly created asset record with the generated QR code
                        Asset::where('id', $record->id)->update(['qr_code' => $qrCode]);

                        $buildingId = $record->building_id;
                        $serviceId = $record->service_id;
                        $assetId = $record->id;
                        $contract = Contract::where('building_id', $buildingId)->where('service_id', $serviceId)->where('end_date', '>=', Carbon::now()->toDateString())->first();
                        if ($contract) {
                            $vendorId = $contract->vendor_id;

                            $asset = Asset::find($assetId);
                            // dd($asset);
                            $technicianVendorIds = DB::table('service_technician_vendor')
                                ->where('service_id', $serviceId)
                                ->pluck('technician_vendor_id');

                            $asset->vendors()->syncWithoutDetaching([$vendorId]);

                            $technicianIds = TechnicianVendor::whereIn('id', $technicianVendorIds)->where('vendor_id', $vendorId)->where('active', true)->pluck('technician_id');
                            if ($technicianIds) {
                                $assignees = User::whereIn('id', $technicianIds)
                                    ->withCount(['assets' => function ($query) {
                                        $query->where('active', true);
                                    }])
                                    ->orderBy('assets_count', 'asc')
                                    ->get();
                                $selectedTechnician = $assignees->first();

                                if ($selectedTechnician) {
                                    $assigned = TechnicianAssets::create([
                                        'asset_id' => $asset->id,
                                        'technician_id' => $selectedTechnician->id,
                                        'vendor_id' => $contract->vendor_id,
                                        'building_id' => $asset->building_id,
                                        'active' => 1,
                                    ]);
                                } else {
                                    Log::info("No technicians to add", []);
                                }
                            }
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                //Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                //Tables\Actions\CreateAction::make(),
            ]);
    }
}
