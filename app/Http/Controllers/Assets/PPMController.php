<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\PPMStoreRequest;
use App\Http\Resources\Assets\ListPPMResource;
use App\Http\Resources\CustomResponseResource;
use App\Models\Vendor\PPM;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class PPMController extends Controller
{

    public function index(Vendor $vendor)
    {
        $assets = $vendor->assets;
        $perPage = 10; // Adjust the number of items per page as needed
        $page = request()->input('page', 1);
        
        $results = $assets->map(function ($asset) {
            $quarters = [1, 2, 3, 4];
        
            $docsSubmitted = collect($quarters)->map(function ($quarter) use ($asset) {
                $ppmEntry = optional($asset->ppms)->where('quarter', $quarter)->first();
                return [
                    'quarter' => $quarter,
                    'quarter_status' => $ppmEntry ? 'submitted' : null,
                    'status' => $ppmEntry ? $ppmEntry->status : null,
                ];
            });
        
            return [
                'asset_id' => $asset->id,
                'asset_name' => $asset->name,
                'quarters' => $docsSubmitted,
            ];
        });
        $paginator = new LengthAwarePaginator(
            $results->forPage($page, $perPage),
            $results->count(),
            $perPage,
            $page
        );
        
        return ListPPMResource::collection($paginator);
    }


    public function store(PPMStoreRequest $request){

        $documentUrl = optimizeDocumentAndUpload($request->file);

        $request->merge([
            "created_by"=> auth()->user()->id,
            "document" => $documentUrl,
            "date" => Carbon::now()->format('Y-m-d'),
        ]);

        $ppm = PPM::create($request->all());

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'PPM created successfully!',
            'status' => 'success',
            'code' => 201,
            'data' => $ppm,
        ]))->response()->setStatusCode(201);
    }
}
