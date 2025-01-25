<?php
namespace App\Http\Controllers\Building;

use App\Http\Controllers\Controller;
use App\Http\Requests\Flat\ImportFlatRequest;
use App\Http\Requests\Flat\StoreFlatRequest;
use App\Http\Requests\Flat\UpdateFlatRequest;
use App\Http\Resources\Building\FlatOwnerResource;
use App\Http\Resources\Building\FlatResource;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Repositories\FlatRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class FlatController extends Controller
{
    public function __construct(FlatRepository $repository)
    {
        $this->model = new Flat;
        $this->repository = $repository;
    }
    public function fetchFlats(Building $building)
    {
        // $flats = $building->flats()->paginate(10);
        $flats = $building->flats()->get();
        return FlatResource::collection($flats);
    }

    // List all flat owners
    public function fetchFlatOwners(Flat $flat)
    {
        // Check if flat exists
        if ($flat) {
            return FlatOwnerResource::collection($flat->owners);
        }
    }

    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $data = $this->repository->list($request);
            return response()->json(["success" => true, "message" => 'Data Found', "error" => [], 'data' => $data->paginate($request->per_page ?? 10)], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function store(StoreFlatRequest $request)
    {
        try {
            $data = $this->repository->store($request->all());
            return response()->json(['success' => true, 'error' => [], 'data' => new FlatResource($data), 'message' => 'Building created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateFlatRequest $request, $id)
    {
        try {
            $data = $this->repository->update($id, $request->all());
            return response()->json(['success' => true, 'error' => [], 'data' => new FlatResource($data), 'message' => 'Record updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->repository->delete($id);
            return response()->json(['success' => true, 'error' => [], 'data' => [], 'message' => 'Record deleted successfully'], 200);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'No query results for model')) {
                return response()->json(['success' => false, 'error' => ['message' => 'Flat not found'], 'data' => []], 500);
            }
            return response()->json(['success' => false, 'error' => ['message' => $e->getMessage()], 'data' => []], 500);
        }
    }

    public function changeStatus($id)
    {
        try {
            $data = $this->repository->changeStatus($id);
            return response()->json(['success' => true, 'error' => [], 'data' => ['id' => $data->id, 'status' => $data->status], 'message' => 'Status updated successfully'], 200);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'No query results for model')) {
                return response()->json(['success' => false, 'error' => ['message' => 'Flat not found'], 'data' => []], 500);
            }
            return response()->json(['success' => false, 'error' => ['message' => $e->getMessage()], 'data' => []], 500);
        }
    }
    public function show($id)
    {
        try {
            $flat = $this->repository->show($id);

            // Using Resource to transform the data
            return response()->json([
                'success' => true,
                'error'   => [],
                'data'    => new FlatResource($flat),
                'message' => 'Flat details retrieved successfully',
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'error'   => [],
                'data'    => [],
                'message' => 'Flat Detail not found',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error retrieving Flat details',
                'error'   => $e->getMessage(),
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
                    // Find building ID by name
                    $building = DB::table('buildings')
                        ->where('name', trim($record['Building']))
                        ->first();

                    // Find owner association ID by name
                    $ownerAssociation = DB::table('owner_associations')
                        ->where('name', trim($record['Owner Association']))
                        ->first();

                    if (! $building) {
                        $errors[] = "Row {$offset}: Building '{$record['Building']}' not found";
                        continue;
                    }

                    if (! $ownerAssociation) {
                        $errors[] = "Row {$offset}: Owner Association '{$record['Owner Association']}' not found";
                        continue;
                    }

                    // Check if record already exists
                    $existingRecord = $this->model->where([
                        'floor'                => $record['Floor'],
                        'building_id'          => $building->id,
                        'owner_association_id' => $ownerAssociation->id,
                    ])->first();

                    if ($existingRecord) {
                        $skippedCount++;
                        continue;
                    }

                    $this->model->create([
                        'floor'                  => $record['Floor'],
                        'building_id'            => $building->id,
                        'owner_association_id'   => $ownerAssociation->id,
                        'description'            => $record['Description'],
                        'property_number'        => $record['Property Number'],
                        'property_type'          => $record['Property Type'],
                        'suit_area'              => $record['Suit Area'],
                        'actual_area'            => $record['Actual Area'],
                        'balcony_area'           => $record['Balcony Area'],
                        'applicable_area'        => $record['Applicable Area'],
                        'virtual_account_number' => $record['Virtual Account Number'],
                        'parking_count'          => $record['Parking Count'],
                        'plot_number'            => $record['Plot Number'],
                        'status'                 => 1,
                        'resource'               => 'Default',
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

    public function export()
    {
        try {
            $filename = "flats-" . date('Y-m-d-His') . ".csv";
            $filepath = storage_path('app/public/' . $filename);

            // Open file
            $file = fopen($filepath, 'w');

            // Add headers
            fputcsv($file, [
                'Floor',
                'Building',
                'Owner Association',
                'Description',
                'Property Number',
                'Property Type',
                'Suit Area',
                'Actual Area',
                'Balcony Area',
                'Applicable Area',
                'Virtual Account Number',
                'Parking Count',
                'Plot Number',
            ]);

            $flats = Flat::select([
                'floor',
                'building_id',
                'owner_association_id',
                'description',
                'property_number',
                'property_type',
                'suit_area',
                'actual_area',
                'balcony_area',
                'applicable_area',
                'virtual_account_number',
                'parking_count',
                'plot_number',
            ])->get();

            foreach ($flats as $flat) {
                fputcsv($file, [
                    $flat->floor,
                    $flat->building->name ?? null,
                    $flat->ownerAssociation->name ?? null,
                    $flat->description,
                    $flat->property_number,
                    $flat->property_type,
                    $flat->suit_area,
                    $flat->actual_area,
                    $flat->balcony_area,
                    $flat->applicable_area,
                    $flat->virtual_account_number,
                    $flat->parking_count,
                    $flat->plot_number,
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
}
