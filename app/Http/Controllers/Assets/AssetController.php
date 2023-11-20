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
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index()
    {
        $assets = TechnicianAssets::where(['technician_id' => auth()->user()->id, 'active' => 1])->get();

        return TechnicianAssetResource::collection($assets);
    }

    public function store(StoreAssetMaintenanceRequest $request)
    {
        $request->merge([
            'status' => 'in-progress',
            'maintenance_date' => now(),
            'maintained_by' => auth()->user()->id
        ]);

        AssetMaintenance::create($request->all());

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Record added successfully!',
            'code' => 201,
            'status' => 'success'
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
