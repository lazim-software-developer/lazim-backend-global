<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use App\Models\Asset;
use App\Models\Assets\Assetmaintenance;
use App\Models\Building\Building;
use App\Models\OwnerAssociation;
use App\Models\TechnicianAssets;
use App\Models\TechnicianVendor;
use App\Models\User\User;
use App\Models\Vendor\Contract;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Vinkla\Hashids\Facades\Hashids;

class CreateAsset extends CreateRecord
{
    protected static string $resource = AssetResource::class;
    protected static ?string $title = 'Create asset';
    // public function afterCreate(): void
    // {
    //     $qrCode = QrCode::size(200)->generate('Asset Name: '.$this->record->name."\n".'Location: '.$this->record->location);

    //     Asset::where('id', $this->record->id)->update(['qr_code' => $qrCode]);
    // }
    public function afterCreate(): void
    {

        // Fetch asset details from the database
        $asset = Asset::where('id', $this->record->id)->first();
        // Fetch Building name
        $building_name = Building::where('id',$asset->building_id)->first();
        $ownerAssociationId = DB::table('building_owner_association')->where('building_id', $asset->building_id)->where('active', true)->first()?->owner_association_id;
        $ownerAssociationName = OwnerAssociation::findOrFail($ownerAssociationId)?->name;
        $assetCode = strtoupper(substr($ownerAssociationName, 0, 2)).'-'. Hashids::encode($this->record->id);
        // dd($assetCode);

        // Build an object with the required properties
        $qrCodeContent = [
            'id' => $this->record->id,
            'asset_code' => $assetCode,
            'asset_id' => $this->record->id,
            'asset_name' => $asset->name,
            'maintenance_status' => 'not-started',
            'building_name' => $building_name->name,
            'building_id' => $asset->building_id,
            'location' => $asset->location,
            'description' => $asset->description,
            // 'last_service_on' => $maintenance->maintenance_date,
        ];

        // Generate a QR code using the QrCode library
        // $qrCode = QrCode::format('svg')->size(200)->generate(json_encode($qrCodeContent));
        $qrCode = QrCode::size(200)->generate(json_encode($qrCodeContent));
        // Log::info('QrCode generated for event: ' . $qrCode);
        $client = new Client();
        $apiKey = env('AWS_LAMBDA_API_KEY');

        try {
            $response = $client->request('GET', env('AWS_LAMBDA_URL'), [
                'headers' => [
                    'x-api-key'    => $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json'    => [
                    'file_name' => $asset->name.'-'.$assetCode,
                    'svg'       => $qrCode->toHtml(),
                ],
            ]);

            $content = json_decode($response->getBody()->getContents());

            $this->record->qr_code = $content->url;     // pass this url to database 
            $this->record->save();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
            $filename = uniqid() . '.' . 'svg';
            $fullPath = 'dev' . '/' . $filename;

            // Read the file's content
            $pdfContent = asset('images/qrcode.svg');

            // Store the file on S3
            Storage::disk('s3')->put($fullPath, $pdfContent, 'public');
        $qrCode = $fullPath;

        // Update the newly created asset record with the generated QR code
        $oa_id = DB::table('building_owner_association')->where('building_id', $this->record->building_id)->where('active', true)->first()?->owner_association_id;
        Asset::where('id', $this->record->id)->update(['qr_code' => $qrCode,'asset_code' => $assetCode, 'owner_association_id' => $oa_id]);

        $buildingId = $this->record->building_id;
        $serviceId = $this->record->service_id;
        $assetId = $this->record->id;
        $contract = Contract::where('building_id', $buildingId)->where('service_id', $serviceId)->where('end_date','>=', Carbon::now()->toDateString())->first();
        if($contract){
            $vendorId = $contract->vendor_id;

            $asset = Asset::find($assetId);
            // dd($asset);
            $technicianVendorIds = DB::table('service_technician_vendor')
                                    ->where('service_id',$serviceId)
                                    ->pluck('technician_vendor_id');

            $asset->vendors()->syncWithoutDetaching([$vendorId]);

            $technicianIds = TechnicianVendor::whereIn('id', $technicianVendorIds)->where('vendor_id',$vendorId)->where('active', true)->pluck('technician_id');
            if ($technicianIds){
                $assignees = User::whereIn('id',$technicianIds)
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
                        'owner_association_id' => $oa_id
                    ]);
                } else {
                    Log::info("No technicians to add", []);
                }
            }
        }
    }

    // protected function getRedirectUrl(): string
    // {
    //     return $this->getResource()::getUrl('view');
    // }

}
