<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCreateRequest;
use App\Http\Requests\Assets\AssetAttachRequest;
use App\Http\Requests\Assets\StoreAssetMaintenanceRequest;
use App\Http\Requests\Assets\UpdateAssetMaintenanceBeforeRequest;
use App\Http\Requests\AssetUpdateRequest;
use App\Http\Resources\Asset\AssetResource;
use App\Http\Resources\Assets\AssetMaintenanceResource;
use App\Http\Resources\Assets\AssetTechniciansResource;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Vendor\AssetListResource;
use App\Models\Asset;
use App\Models\Assets\Assetmaintenance;
use App\Models\Building\Building;
use App\Models\OwnerAssociation;
use App\Models\TechnicianAssets;
use App\Models\Vendor\Vendor;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Vinkla\Hashids\Facades\Hashids;

class AssetController extends Controller
{
    public function index()
    {
        $technicianId = auth()->user()->id;
        // $currentQuarterStart = Carbon::now()->firstOfQuarter();
        // $currentQuarterEnd = Carbon::now()->lastOfQuarter();

        // Paginate the query results before mapping
        $assignedAssets = TechnicianAssets::with(['asset', 'assetMaintenances'])
            ->where('technician_id', $technicianId)
            ->paginate(10); // Set the number of items per page

        // Transform the paginated results
        $transformedAssets = $assignedAssets->getCollection()->map(function ($technicianAsset) {
            $latestMaintenance = $technicianAsset->assetMaintenances->last();
            $status = 'not-started';
            $id = null;
            $last_date =  $latestMaintenance?->maintenance_date;

            if ($latestMaintenance && Carbon::parse($latestMaintenance->maintenance_date)->addDays($technicianAsset->asset->frequency_of_service) > now()->toDateString()) {
                $status = $latestMaintenance->status;
                $id = $latestMaintenance->id;
            }

            return [
                'id' => $id,
                'technician_asset_id' => $technicianAsset->id,
                'asset_id' => $technicianAsset->asset_id,
                'asset_name' => $technicianAsset->asset->name,
                'maintenance_status' => $status,
                'building_name' => $technicianAsset->building->name,
                'building_id' => $technicianAsset->building->id,
                'location' => $technicianAsset->asset->location,
                'description' => $technicianAsset->asset->description,
                'last_service_on' => $last_date,
                'frequency_of_service' => $technicianAsset->asset->frequency_of_service
            ];
        });

        // Update the original paginated object's collection
        $assignedAssets->setCollection($transformedAssets);

        return response()->json($assignedAssets);
    }

    public function store(StoreAssetMaintenanceRequest $request)
    {
        $imagePath = optimizeAndUpload($request->media, 'dev');

        // Create JSON data
        $jsonData = [
            'comment' => [
                'before' => $request->input('comment', ''),
                'after' => ''
            ],
            'media' => [
                'before' => $imagePath ?? null,
                'after' => ''
            ]
        ];
        $oa_id = DB::table('building_owner_association')->where('building_id', $request->building_id)->where('active', true)->first()?->owner_association_id;
        $data = AssetMaintenance::create([
            'maintenance_date' => now(),
            'comment' => json_encode($jsonData['comment']),
            'media' => json_encode($jsonData['media']),
            'maintained_by' => auth()->user()->id,
            'building_id' => $request->building_id,
            'status' => 'in-progress',
            'technician_asset_id' => $request->technician_asset_id,
            'owner_association_id' => $oa_id,
        ]);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Record added successfully!',
            'code' => 201,
            'status' => 'success',
            'data' => $data
        ]))->response()->setStatusCode(201);
    }

    public function updateBefore(UpdateAssetMaintenanceBeforeRequest $request, AssetMaintenance $assetMaintenance)
    {
        $imagePath = optimizeAndUpload($request->media, 'dev');

        // Create JSON data
        $commentData = json_decode($assetMaintenance->comment, true);
        $mediaData = json_decode($assetMaintenance->media, true);

        // Update the 'after' part for comment
        $commentData['after'] = $request->input('comment');
        $mediaData['after'] = $imagePath;

        $assetMaintenance->update([
            'comment' => json_encode($commentData),
            'media' => json_encode($mediaData),
            'status' => 'completed'
        ]);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Record updated successfully!',
            'code' => 200,
        ]))->response()->setStatusCode(200);
    }

    // List all entries for an asset
    public function fetchAssetMaintenances(TechnicianAssets $technicianasset) {
        $assets = Assetmaintenance::where(
            ['technician_asset_id' => $technicianasset->id, 'maintained_by' => auth()->user()->id]
        )->latest()->paginate();

        return AssetMaintenanceResource::collection($assets);
    }

    //Listing assets for vendor
    public function listAssets(Vendor $vendor,Request $request){
        $assets = $vendor->assets()
            ->when($request->filled('type'), function ($query) use ($vendor, $request) {
                $buildings = $vendor->buildings->where('pivot.active', true)->where('pivot.end_date', '>', now()->toDateString())->unique()
                                ->filter(function($buildings) use($request){
                                        return $buildings->ownerAssociations->contains('role',$request->type);
                                });
                $query->whereIn('building_id', $buildings->pluck('id'));
            });
        return AssetListResource::collection($assets->paginate(10));
    }

    public function attachAsset(AssetAttachRequest $request,Asset $asset){
        $assets = TechnicianAssets::firstOrCreate([
            'asset_id' => $asset->id,
            'technician_id' => $request->technician_id,
            'vendor_id' => $request->vendor_id,
            'building_id' => $request->building_id,
        ],
        ['active' => true]);
        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Asset attach successfully!',
            'code' => 201,
            'status' => 'success',
            'data' => $assets,
        ]))->response()->setStatusCode(201);
    }

    public function listTechnicians(Asset $asset){
            $technicians = $asset->users;
            return AssetTechniciansResource::collection($technicians);
    }

    public function create(Vendor $vendor, AssetCreateRequest $request)
    {
        $request['owner_association_id'] = auth()->user()->owner_association_id;
        $asset = Asset::create($request->all());
        // Fetch Building name
        $building_name        = Building::where('id', $asset->building_id)->first();
        $ownerAssociationId   = DB::table('building_owner_association')->where('building_id', $asset->building_id)->where('active', true)->first()?->owner_association_id;
        $ownerAssociationName = OwnerAssociation::findOrFail($ownerAssociationId)?->name;
        $assetCode            = strtoupper(substr($ownerAssociationName, 0, 2)) . '-' . Hashids::encode($asset->id);

        // Build an object with the required properties
        $qrCodeContent = [
            'id'                 => $asset->id,
            'asset_code'         => $assetCode,
            'asset_id'           => $asset->id,
            'asset_name'         => $asset->name,
            'maintenance_status' => 'not-started',
            'building_name'      => $building_name->name,
            'building_id'        => $asset->building_id,
            'location'           => $asset->location,
            'description'        => $asset->description,
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
                    'file_name' => $asset->name . '-' . $assetCode,
                    'svg'       => $qrCode->toHtml(),
                ],
                'verify'  => false,
            ]);

            $content = json_decode($response->getBody()->getContents());
            $asset->qr_code = $content->url;
            $asset->asset_code = $assetCode;
            $asset->save();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        // Attaching to vendor
        $asset->vendors()->attach($vendor->id);

        return (new CustomResponseResource([
            'title'   => 'Asset created successful!',
            'message' => "",
            'code'    => 201,
            'status'  => 'success',
        ]))->response()->setStatusCode(200);

    }

    public function showAsset(Vendor $vendor, Asset $asset)
    {
        return AssetResource::make($asset);
    }
    public function updateAsset(Vendor $vendor, Asset $asset, AssetUpdateRequest $request)
    {
        $asset->update($request->all());
        $building_name = Building::where('id', $asset->building_id)->first();

        // Build an object with the required properties
        $qrCodeContent = [
            'id'                 => $asset->id,
            'asset_code'         => $asset->asset_code,
            'asset_id'           => $asset->id,
            'asset_name'         => $asset->name,
            'maintenance_status' => 'not-started',
            'building_name'      => $building_name->name,
            'building_id'        => $asset->building_id,
            'location'           => $asset->location,
            'description'        => $asset->description,
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
                    'file_name' => $asset->name . '-' . $asset->asset_code,
                    'svg'       => $qrCode->toHtml(),
                ],
                'verify'  => false,
            ]);

            $content           = json_decode($response->getBody()->getContents());
            $asset->qr_code    = $content->url;
            $asset->save();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }


        return AssetResource::make($asset);
    }
}
