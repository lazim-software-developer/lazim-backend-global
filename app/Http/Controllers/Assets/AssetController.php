<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\StoreAssetMaintenanceRequest;
use App\Http\Requests\Assets\UpdateAssetMaintenanceAfterRequest;
use App\Http\Requests\Assets\UpdateAssetMaintenanceBeforeRequest;
use App\Http\Resources\Assets\TechnicianAssetResource;
use App\Http\Resources\CustomResponseResource;
use App\Models\Assets\Assetmaintenance;
use App\Models\TechnicianAssets;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index()
    {
        // $assets = TechnicianAssets::where(['technician_id' => auth()->user()->id, 'active' => 1])->get();

        // return TechnicianAssetResource::collection($assets);

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

        $request->merge([
            'comment' => json_encode($jsonData['comment']),
            'media' => json_encode($jsonData['media']),
            'status' => 'in-progress',
            'maintenance_date' => now(),
            'maintained_by' => auth()->user()->id
        ]);

        $data = AssetMaintenance::create($request->all());

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

        $assetMaintenance->update([
            'comment' => json_encode($jsonData['comment']),
            'media' => json_encode($jsonData['media'])
        ]);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Record updated successfully!',
            'code' => 200,
        ]))->response()->setStatusCode(200);
    }

    public function updateAfter(UpdateAssetMaintenanceAfterRequest $request, AssetMaintenance $assetMaintenance)
    {
        $imagePath = optimizeAndUpload($request->media, 'dev');

        // Fetch and decode the existing JSON data
        $commentData = json_decode($assetMaintenance->comment, true);
        $mediaData = json_decode($assetMaintenance->media, true);

        // Update the 'after' part for comment
        $commentData['after'] = $request->input('comment');
        $mediaData['after'] = $imagePath;

        $request->merge([
            'status' => 'completed'
        ]);

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
}
