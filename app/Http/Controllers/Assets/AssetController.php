<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\AssetAttachRequest;
use App\Http\Requests\Assets\StoreAssetMaintenanceRequest;
use App\Http\Requests\Assets\UpdateAssetMaintenanceBeforeRequest;
use App\Http\Resources\Assets\AssetMaintenanceResource;
use App\Http\Resources\Assets\AssetTechniciansResource;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Vendor\AssetListResource;
use App\Models\Asset;
use App\Models\Assets\Assetmaintenance;
use App\Models\TechnicianAssets;
use App\Models\Vendor\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;

class AssetController extends Controller
{
    public function index()
    {
        $technicianId = auth()->user()->id;
        $currentQuarterStart = Carbon::now()->firstOfQuarter();
        $currentQuarterEnd = Carbon::now()->lastOfQuarter();

        // Paginate the query results before mapping
        $assignedAssets = TechnicianAssets::with(['asset', 'assetMaintenances' => function ($query) use ($currentQuarterStart, $currentQuarterEnd) {
            $query->whereBetween('maintenance_date', [$currentQuarterStart, $currentQuarterEnd]);
        }])
            ->where('technician_id', $technicianId)
            ->paginate(10); // Set the number of items per page

        // Transform the paginated results
        $transformedAssets = $assignedAssets->getCollection()->map(function ($technicianAsset) {
            $latestMaintenance = $technicianAsset->assetMaintenances->last();
            $status = 'not-started';
            $id = null;
            $last_date =  null;

            if ($latestMaintenance) {
                $status = $latestMaintenance->status;
                $id = $latestMaintenance->id;
                $last_date = $latestMaintenance->maintenance_date;
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
                'last_service_on' => $last_date
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

        $data = AssetMaintenance::create([
            'maintenance_date' => now(),
            'comment' => json_encode($jsonData['comment']),
            'media' => json_encode($jsonData['media']),
            'maintained_by' => auth()->user()->id,
            'building_id' => $request->building_id,
            'status' => 'in-progress',
            'technician_asset_id' => $request->technician_asset_id,
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
    public function listAssets(Vendor $vendor){
        $assets = $vendor->assets;
        return AssetListResource::collection($assets);
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
}
