<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\Building\Building;
use App\Models\OwnerAssociation;
use App\Models\TechnicianAssets;
use App\Models\TechnicianVendor;
use App\Models\User\User;
use App\Models\Vendor\Contract;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Vinkla\Hashids\Facades\Hashids;

class AssetsListImport implements ToCollection, WithHeadingRow
{

    protected $buildingId;
    protected $serviceId;

    public function __construct($buildingId, $serviceId)
    {

        $this->buildingId = $buildingId;
        $this->serviceId  = $serviceId;
    }
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        // dd($rows);
        $expectedHeadings = [
            'asset_name',
            'location',
            'floor',
            'division',
            'discipline',
            'frequency_of_service',
            'description',
        ];

        if($rows->first()== null){
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("You have uploaded an empty file")
                ->send();
            return 'failure';
        }

        if ($rows->first()->filter()->isEmpty()) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("You have uploaded an empty file")
                ->send();
            return 'failure';
        }

        // Extract the headings from the first row
        $extractedHeadings = array_keys($rows->first()->toArray());

        // Check if all expected headings are present in the extracted headings
        $missingHeadings = array_diff($expectedHeadings, $extractedHeadings);

        if (!empty($missingHeadings)) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("Missing headings: " . implode(', ', $missingHeadings))
                ->send();
            return 'failure';
        } else {

            $missingFieldsRows = [];

            foreach ($rows as $index => $row) {
                // Check if any of the required fields are null
                if (empty($row['asset_name']) || empty($row['floor']) || empty($row['location']) || empty($row['division']) || empty($row['discipline']) || empty($row['frequency_of_service'])) {
                    $missingFieldsRows[] = $index + 1; // Add the row number to the array
                }
            }

            if (!empty($missingFieldsRows)) {
                // If there are rows with missing fields, show an error message with the row numbers
                Notification::make()
                    ->title("Upload valid excel file.")
                    ->danger()
                    ->body("Required fields are missing in the following row(s): " . implode(', ', $missingFieldsRows))
                    ->send();
                return 'failure';
            }

            foreach ($rows as $row) {
                $buildingId = $this->buildingId;
                $serviceId  = $this->serviceId;
                $oa_id = DB::table('building_owner_association')->where('building_id', $buildingId)->where('active', true)->first()?->owner_association_id;

                if ($row['asset_name'] && $row['floor'] && $row['location'] && $row['division'] && $row['discipline'] && $row['frequency_of_service']) {

                }
                $asset = Asset::firstOrCreate(
                    [
                        'building_id' => $buildingId,
                        'name'        => $row['asset_name'],
                        'service_id'  => $serviceId,
                        'floor'       => $row['floor'],
                        'location'    => $row['location'],
                    ],
                    [
                        'division'             => $row['division'],
                        'discipline'           => $row['discipline'],
                        'frequency_of_service' => $row['frequency_of_service'],
                        'description'          => $row['description'],
                        'owner_association_id' => $oa_id
                    ]
                );

                // Fetch Building name
                $building_name = Building::where('id', $asset->building_id)->first();
                $oam_id = DB::table('building_owner_association')->where('building_id',$asset->building_id)->where('active', true)->first();
                $oam = OwnerAssociation::find($oam_id?->owner_association_id?:auth()->user()->ownerAssociation->first()->id);
                $assetCode     = strtoupper(substr($oam?->name, 0, 2)) . '-' . Hashids::encode($asset->id);
                // dd($assetCode);

                // Build an object with the required properties
                $qrCodeContent = [
                    'id'                   => $asset->id,
                    'asset_code'           => $assetCode,
                    'asset_id'             => $asset->id,
                    'asset_name'           => $asset->name,
                    'maintenance_status'   => 'not-started',
                    'building_name'        => $building_name->name,
                    'building_id'          => $asset->building_id,
                    'floor'                => $asset->floor,
                    'location'             => $asset->location,
                    'division'             => $asset->division,
                    'discipline'           => $asset->discipline,
                    'frequency_of_service' => $asset->frequency_of_service,
                    'description'          => $asset->description,
                    // 'last_service_on' => $maintenance->maintenance_date,
                ];

                // Generate a QR code using the QrCode library
                $qrCode = QrCode::size(200)->generate(json_encode($qrCodeContent));

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
                        'verify'=>false,
                    ]);
        
                    $content = json_decode($response->getBody()->getContents());
                    
                    // $this->record->qr_code = $content->url;  
                      // pass this url to database 
                    // $this->record->save();
                    $asset->update([
                        'qr_code' => $content->url,
                        'asset_code' => $assetCode
                    ]);
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }

                // Update the newly created asset record with the generated QR code
                // Asset::where('id', $asset->id)->update(['qr_code' => $qrCode, 'asset_code' => $assetCode]);

                $buildingId = $asset->building_id;
                $serviceId  = $asset->service_id;
                $assetId    = $asset->id;
                $contract   = Contract::where('building_id', $buildingId)->where('service_id', $serviceId)->where('end_date', '>=', Carbon::now()->toDateString())->first();
                $oa_id = DB::table('building_owner_association')->where('building_id', $buildingId)->where('active', true)->first()?->owner_association_id;
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
                                'owner_association_id' => $oa_id
                            ]);
                        } else {
                            //
                        }
                    }
                }
            }
            Notification::make()
                ->title("Details uploaded successfully")
                ->success()
                ->send();
            return 'success';
        }
    }
}
