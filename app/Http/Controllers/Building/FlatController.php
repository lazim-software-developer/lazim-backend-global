<?php

namespace App\Http\Controllers\Building;
use Illuminate\Http\Request;
use App\Models\Building\Flat;
use App\Models\Building\Building;
use App\Http\Controllers\Controller;
use App\Repositories\FlatRepository;
use App\Http\Resources\Building\FlatResource;
use App\Http\Resources\Building\FlatOwnerResource;

class FlatController extends Controller
{
    public function __construct(FlatRepository $repository)
    {
        $this->repository = $repository;
    }
    public function fetchFlats(Building $building)
    {
        // $flats = $building->flats()->paginate(10);
        $flats = $building->flats()->get();
        return FlatResource::collection($flats);
    }

    // List all flat owners
    public function fetchFlatOwners(Flat $flat) {
        // Check if flat exists
        if($flat) {
            return FlatOwnerResource::collection($flat->owners);
        }
    }

    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $data = $this->repository->list($request);
            return response()->json(["success"=>true,"message"=>'Data Found',"error"=>[],'data' => $data->paginate($request->per_page ?? 10)], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function store(StoreFlatRequest $request)
    {
        try {
            $data = $this->repository->store($request->all());
            return response()->json(['success' => true,'error' => [],'data' => new FlatResource($data), 'message' => 'Building created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateFlatRequest $request, $id)
    {
        try {
            $data = $this->repository->update($id, $request->all());
            return response()->json(['success' => true,'error' => [],'data' =>  new FlatResource($data), 'message' => 'Record updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->repository->delete($id);
            return response()->json(['success' => true,'error' => [],'data' =>  [],'message' => 'Record deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function changeStatus($id)
    {
        try {
            $data = $this->repository->changeStatus($id);
            return response()->json(['success' => true,'error' => [],'data' => ['id'=>$data->id,'status'=>$data->status], 'message' => 'Status updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function show($id)
    {
        try {
            $building = $this->repository->show($id);
            
            // Using Resource to transform the data
            return response()->json([
                'success' => true,
                'error' => [],
                'data' => new FlatResource($building),
                'message' => 'Flat details retrieved successfully'
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Flat Detail not found'
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving Flat details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
