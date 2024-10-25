<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use App\Forms\Components\QrCode;
use App\Models\Asset;
use App\Models\Assets\Assetmaintenance;
use App\Models\Building\Building;
use App\Models\Master\Service;
use App\Models\OwnerAssociation;
use App\Models\TechnicianAssets;
use App\Models\TechnicianVendor;
use App\Models\User\User;
use App\Models\Vendor\Contract;
use App\Models\Vendor\Vendor;
use Carbon\Carbon;
use Closure;
use Filament\Facades\Filament;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Vinkla\Hashids\Facades\Hashids;

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
                                fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                    if (Asset::where('building_id', $get('building_id'))->where('name', $value)->exists()) {
                                        $fail('The Name is already taken for this Building.');
                                    }
                                },
                            ])
                            ->required()
                            ->label('Asset Name'),
                        TextInput::make('floor')
                            ->required()
                            ->rules(['max:50']),
                        TextInput::make('location')
                            ->required()
                            ->rules(['max:50', 'regex:/^(?=.*[a-zA-Z])[a-zA-Z0-9\s!@#$%^&*_+\-=,.]*$/'])
                            ->label('Spot'),
                        TextInput::make('division')
                            ->required()
                            ->rules(['max:50']),
                        TextInput::make('discipline')
                            ->required()
                            ->rules(['max:50']),
                        TextInput::make('frequency_of_service')
                            ->required()->integer()->suffix('days')->minValue(1),
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
                        TextInput::make('asset_code')
                            ->visible(function (callable $get) {
                                if ($get('asset_code') != null) {
                                    return true;
                                }
                                return false;
                            }),
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
                TextColumn::make('name')->searchable()->label('Asset name'),
                TextColumn::make('description')->searchable()->default('--')->label('Description'),
                TextColumn::make('location')->label('Location'),
                TextColumn::make('service.name')->searchable()->label('Service'),
                TextColumn::make('building.name')->searchable()->label('Building'),
                TextColumn::make('asset_code'),
                TextColumn::make('vendors.name')->default('--')
                    ->searchable()->label('Vendor'),
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
                        $building_name        = Building::where('id', $asset->building_id)->first();
                        $oa_id                = DB::table('building_owner_association')->where('building_id', $asset->building_id)->where('active', true)->first()?->owner_association_id;
                        $ownerAssociationName = OwnerAssociation::findOrFail($oa_id)?->name;

                        // Fetch maintenance details from the database
                        $maintenance = Assetmaintenance::where('technician_asset_id', $technician_asset_id)->first();
                        $assetCode   = strtoupper(substr($ownerAssociationName, 0, 2)) . '-' . Hashids::encode($record->id);

                        // Build an object with the required properties
                        $qrCodeContent = [
                            'id'                  => $record->id,
                            'asset_code'          => $assetCode,
                            'technician_asset_id' => $technician_asset_id,
                            'asset_id'            => $record->id,
                            'asset_name'          => $asset->name,
                            'maintenance_status'  => 'not-started',
                            'building_name'       => $building_name->name,
                            'building_id'         => $asset->building_id,
                            'location'            => $asset->location,
                            'description'         => $asset->description,
                            // 'last_service_on' => $maintenance->maintenance_date,
                        ];

                        // Generate a QR code using the QrCode library
                        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->generate(json_encode($qrCodeContent));

                        $client = new Client();

                        try {
                            $response = $client->request('GET', env('AWS_LAMBDA_URL'), [
                                'headers' => [
                                    'x-api-key'    => env('AWS_LAMBDA_API_KEY'),
                                    'Content-Type' => 'application/json',
                                ],
                                'json'    => [
                                    'file_name' => $record->name . '-' . $assetCode,
                                    'svg'       => $qrCode->toHtml(),
                                ],
                                'verify'  => false,
                            ]);

                            $content = json_decode($response->getBody()->getContents());

                            // Update with S3 URL
                            Asset::where('id', $record->id)->update([
                                'qr_code'              => $content->url,
                                'asset_code'           => $assetCode,
                                'owner_association_id' => $oa_id,
                            ]);

                        } catch (\Exception $e) {
                            Log::error($e->getMessage());
                        }

                        $buildingId = $record->building_id;
                        $serviceId  = $record->service_id;
                        $assetId    = $record->id;
                        $contract   = Contract::where('building_id', $buildingId)->where('service_id', $serviceId)->where('end_date', '>=', Carbon::now()->toDateString())->first();
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
                                        'asset_id'      => $asset->id,
                                        'technician_id' => $selectedTechnician->id,
                                        'vendor_id'     => $contract->vendor_id,
                                        'building_id'   => $asset->building_id,
                                        'active'        => 1,
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
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('attach')
                        ->form([
                            Select::make('vendor_id')
                                ->required()
                                ->relationship('vendors', 'name')
                                ->options(function () {
                                    return Vendor::whereHas('ownerAssociation', function ($query) {
                                        $oaId = auth()->user()?->owner_association_id;
                                        if (auth()->user()->role->name == 'Property Manager') {
                                            $query->where('owner_association_id', $oaId)
                                                ->where('status', 'approved');
                                        } else {
                                            $query->where('owner_association_id', Filament::getTenant()->id)
                                                ->where('status', 'approved');
                                        }

                                    })
                                        ->pluck('name', 'id');
                                }),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $vendorId = $data['vendor_id'];
                            foreach ($records as $record) {
                                $record->vendors()->sync([$vendorId]);
                            }
                            Notification::make()
                                ->title("Vendor attached successfully")
                                ->success()
                                ->send();
                        })->label('Attach Vendor'),
                ]),
            ])
            ->emptyStateActions([
                //Tables\Actions\CreateAction::make(),
            ]);
    }
}
