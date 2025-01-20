<?php

namespace App\Http\Controllers\Building;

use Illuminate\Http\Request;
use App\Models\Building\Building;
use App\Http\Controllers\Controller;
use App\Repositories\BuildingRepository;
use App\Http\Resources\Building\BuildingResource;
use App\Http\Resources\Building\BuildingsResource;
use App\Http\Requests\Building\StoreBuildingRequest;
use App\Http\Requests\Building\UpdateBuildingRequest;
use App\Http\Resources\Building\BuildingResourceCollection;

class BuildingController extends Controller
{

    public function __construct(BuildingRepository $repository)
    {
        $this->repository = $repository;
    }
    /**
     * Display a listing of the resource.
     */
    public function fetchbuildings(Request $request)
    {
        $query = Building::query();
    
        if ($request->has('type') && $request->type === 'globalOa') {
            $query->where('resource', 'Default');
        }
        
        $buildings = $query->get();
        return BuildingsResource::collection($buildings);
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

    public function store(StoreBuildingRequest $request)
    {
        try {
            $data = $this->repository->store($request->all());
            return response()->json(['success' => true,'error' => [],'data' => new BuildingResource($data), 'message' => 'Building created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateBuildingRequest $request, $id)
    {
        try {
            $data = $this->repository->update($id, $request->all());
            return response()->json(['success' => true,'error' => [],'data' =>  new BuildingResource($data), 'message' => 'Record updated successfully'], 200);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'No query results for model')) {
                return response()->json(['success' => false,'error' => ['message' => 'Flat not found'],'data' =>  []], 500);
            }
            return response()->json(['success' => false,'error' => ['message' => $e->getMessage()],'data' =>  []], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->repository->delete($id);
            return response()->json(['success' => true,'error' => [],'data' =>  [],'message' => 'Record deleted successfully'], 200);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'No query results for model')) {
                return response()->json(['success' => false,'error' => ['message' => 'Flat not found'],'data' =>  []], 500);
            }
            return response()->json(['success' => false,'error' => ['message' => $e->getMessage()],'data' =>  []], 500);
        }
    }

    public function changeStatus($id)
    {
        try {
            $data = $this->repository->changeStatus($id);
            return response()->json(['success' => true,'error' => [],'data' => ['id'=>$data->id,'status'=>$data->status], 'message' => 'Status updated successfully'], 200);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'No query results for model')) {
                return response()->json(['success' => false,'error' => ['message' => 'Flat not found'],'data' =>  []], 500);
            }
            return response()->json(['success' => false,'error' => ['message' => $e->getMessage()],'data' =>  []], 500);
        }
    }
    // app/Http/Controllers/Api/OwnerAssociationController.php

    public function show($id)
    {
        try {
            $building = $this->repository->show($id);
            
            // Using Resource to transform the data
            return response()->json([
                'success' => true,
                'error' => [],
                'data' => new BuildingResource($building),
                'message' => 'Building details retrieved successfully'
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'data' => [],
                'error' => [],
                'message' => 'Building Detail not found'
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving owner association details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
