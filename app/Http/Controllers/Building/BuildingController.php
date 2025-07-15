<?php

namespace App\Http\Controllers\Building;

use League\Csv\Reader;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\OwnerAssociation;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\BuildingRepository;
use App\Http\Requests\Flat\ImportFlatRequest;
use App\Http\Resources\Building\BuildingResource;
use App\Http\Resources\Building\BuildingsResource;
use App\Http\Requests\Building\StoreBuildingRequest;
use App\Http\Requests\Building\UpdateBuildingRequest;
use App\Http\Resources\Building\BuildingResourceCollection;

class BuildingController extends Controller
{

    public function __construct(BuildingRepository $repository)
    {
        $this->model = new Building;
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
        $query = Building::query();

        // if ($request->has('type')) {
        //     $type = $request->input('type');
        //     $query->whereHas('ownerAssociations', function($q) use ($type) {
        //         $q->where('role', $type);
        //     });
        // }

        // if($request->registration){
        //     $activeBuildings = DB::table('building_owner_association')
        //         ->whereIn('building_id', $query->pluck('id'))
        //         ->where('active', true)
        //         ->pluck('building_id');
        //     $query = Building::whereIn('id',$activeBuildings);
        // }

        $buildings = $query->get();
        return BuildingResource::collection($buildings);
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

    public function import(ImportFlatRequest $request)
    {
        try {
            $file = $request->file('file');
            $csv  = Reader::createFromPath($file->getPathname(), 'r');
            $csv->setHeaderOffset(0);

            $records       = $csv->getRecords();
            $importedCount = 0;
            $skippedCount  = 0;
            $errors        = [];

            DB::beginTransaction();

            foreach ($records as $offset => $record) {
                try {
                    if(count($record)!=11){
                    return response()->json([
                        'message'  => 'Import failed',
                        'data'    => [],
                        'errors'   => 'The CSV file format is invalid. Please review the sample file format for importing data.',
                        'status'   => false,
                        'imported' => 0,
                        'skipped'  => 0,
                    ], 422);
                    }
                    // Find City ID by name
                    $city = DB::table('cities')
                        ->where('name', trim($record['City']))
                        ->first();
                    // Find owner association ID by name
                    $ownerAssociation = DB::table('owner_associations')
                        ->where('name', trim($record['Owner Association']))
                        ->first();

                    if (! $city) {
                        $errors[] = "Row {$offset}: City '{$record['City']}' not found";
                        continue;
                    }
                    if (! $ownerAssociation) {
                        $errors[] = "Row {$offset}: Owner Association '{$record['Owner Association']}' not found";
                        continue;
                    }

                    // Check if record already exists
                    $existingRecord = $this->model->where([
                        'name'                => $record['Name'],
                        'property_group_id'                => $record['Property Group ID'],
                        'area'                => $record['Area'],
                        'city_id'                => $city->id,
                        'owner_association_id' => $ownerAssociation->id,
                    ])->first();

                    if ($existingRecord) {
                        $skippedCount++;
                        continue;
                    }

                    $this->model->create([
                        'name'                   => $record['Name'],
                        'slug'                   => rand(1000,9999).Str::slug($record['Name'])??NULL,
                        'property_group_id'      => $record['Property Group ID']??NULL,
                        'address_line1'            => $record['Address Line 1']??NULL,
                        'address_line2'        => $record['Address Line 2']??NULL,
                        'area'          => $record['Area'],
                        'city_id'              => $city->id,
                        'description'            => $record['Description']??NULL,
                        'floors'           => $record['Floors']??NULL,
                        'owner_association_id'        => $ownerAssociation->id,
                        'allow_postupload' => strtolower($record['Allow Post Upload'])== 'false' ? 0 : 1,
                        'show_inhouse_services'          => strtolower($record['Show InHouse Services'])== 'false' ? 0 : 1,
                        'resource'            => 'Default',
                        'status'                 => 1,
                    ]);

                    $importedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$offset}: " . $e->getMessage();
                }
            }

            if (empty($errors)) {
                DB::commit();
                return response()->json([
                    'message'  => "{$importedCount} records imported successfully, {$skippedCount} records skipped (already exist)",
                    'status'   => true,
                    'data'    => [],
                    'errors'    => $errors,
                    'imported' => $importedCount,
                    'skipped'  => $skippedCount,
                ], 200);
            } else {
                DB::rollBack();
                return response()->json([
                    'message'  => 'Import failed',
                    'data'    => [],
                    'errors'   => $errors,
                    'status'   => false,
                    'imported' => $importedCount,
                    'skipped'  => $skippedCount,
                ], 422);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Import failed',
                'data'    => [],
                'error'   => $e->getMessage(),
                'status'  => false,
            ], 500);
        }
    }
}
