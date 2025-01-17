<?php

namespace App\Http\Controllers\Building;

use Illuminate\Http\Request;
use App\Models\OwnerAssociation;
use App\Models\Building\Building;
use App\Http\Controllers\Controller;
use App\Repositories\BuildingRepository;
use App\Http\Resources\Building\BuildingResource;
use App\Http\Requests\Building\StoreBuildingRequest;
use App\Http\Requests\Building\UpdateBuildingRequest;

class BuildingController extends Controller
{

    public function __construct(BuildingRepository $repository)
    {
        $this->repository = $repository;
    }
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     $buildings = Building::get();
        
    //     return new BuildingResourceCollection($buildings);
    // }
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

    public function export()
    {
        try {
            $filename = "buildings-" . date('Y-m-d-His') . ".csv";
            $filepath = storage_path('app/public/' . $filename);

            // Open file
            $file = fopen($filepath, 'w');

            // Add headers
            fputcsv($file, [
                'Name',
                'Owner Association',
                'Property Group ID',
                'Address Line 1',
                'Address Line 2',
                'Area',
                'City',
                'Description',
                'Floors',
                'Allow Post Upload',
                'Show InHouse Services',
                'Status',
                'Create Date',
                'Cover Photo',
            ]);

            $buildings = Building::select([
                'name',
                'cover_photo',
                'property_group_id',
                'address_line1',
                'address_line2',
                'area',
                'city_id',
                'description',
                'floors',
                'owner_association_id',
                'allow_postupload',
                'show_inhouse_services',
                'status',
                'created_at',
            ])->get();

            foreach ($buildings as $building) {
                $ownerAssociationName=OwnerAssociation::where('id',$building->owner_association_id)->value('name');
                fputcsv($file, [
                    $building->name,
                    $ownerAssociationName,
                    $building->property_group_id,
                    $building->address_line1,
                    $building->address_line2,
                    $building->area,
                    $building->cities->name ?? null,
                    $building->description,
                    $building->floors,
                    $building->allow_postupload=== 1 ? 'TRUE' : 'FALSE', 
                    $building->show_inhouse_services=== 1 ? 'TRUE' : 'FALSE',
                    $building->status=== 1 ? 'Active' : 'In-Active',
                    $building->created_at,
                    $building->cover_photo,
                ]);
            }
            fclose($file);
            die('asds');
            return response()->download($filepath, $filename, [
                'Content-Type' => 'text/csv',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Export failed',
                'data'    => [],
                'error'   => $e->getMessage(),
                'status'  => false,
            ], 500);
        }
    }
}
