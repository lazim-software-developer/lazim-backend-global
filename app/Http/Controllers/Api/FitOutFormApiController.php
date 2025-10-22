<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\FitOutFormResource;
use App\Models\Forms\FitOutForm;

class FitOutFormApiController extends Controller
{
    /**
     * @api {get} /fitout/list List Fit-Out Forms
     * @apiDescription
     * Returns a paginated list of Fit-Out forms with optional filters.
     *  
     * **Authentication Required:** Yes (Sanctum)
     *
     * @apiHeader {String} Authorization Bearer token required.
     * @apiHeader {String} Accept application/json
     *
     * @apiBody {Integer} [building_id] Optional. Filter by building.
     * @apiBody {Integer} [flat_id] Optional. Filter by flat.
     * @apiBody {String="all","pending","approved","rejected"} [status] Optional. Filter by status.
     *
     * @apiSuccessExample {json} Success Response:
     * {
     *   "success": true,
     *   "error": [],
     *   "data": [
     *     {
     *       "id": 1,
     *       "ticket_number": "FITOUT-1001",
     *       "contractor_name": "ABC Interiors",
     *       "building": {"id": 1, "name": "Skyline Towers"},
     *       "flat": {"id": 3, "property_number": "B-301"},
     *       "status": "approved",
     *       "remarks": "Documents verified"
     *     }
     *   ],
     *   "message": "Fit-Out form list fetched successfully."
     * }
     */
    public function list(Request $request)
    {
        try {
            $query = FitOutForm::query()
                ->with(['building:id,name', 'flat:id,property_number'])
                ->orderByDesc('created_at');

            // Optional filters
            if ($request->filled('building_id')) {
                $query->where('building_id', $request->building_id);
            }

            if ($request->filled('flat_id')) {
                $query->where('flat_id', $request->flat_id);
            }

            // Optimized status handling
            if ($request->filled('status') && $request->status !== 'all') {
                $status = $request->status;

                $query->when($status === 'pending', function ($q) {
                    $q->where(fn($q) =>
                        $q->whereNull('status')
                          ->orWhereNotIn('status', ['approved', 'rejected'])
                    );
                })
                ->when(in_array($status, ['approved', 'rejected']), function ($q) use ($status) {
                    $q->where('status', $status);
                });
            }

            $records = $query->paginate(10);

            return response()->json([
                'success' => true,
                'error' => [],
                'data' => FitOutFormResource::collection($records),
                'message' => 'Fit-Out form list fetched successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('### Api ## FitOutFormApiController@list ## ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => null,
                'message' => 'Something went wrong.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @api {get} /fitout/{id} Get Fit-Out Form Details
     * @apiDescription
     * Fetch detailed information about a specific Fit-Out form.
     *
     * @apiParam {Integer} id FitOutForm ID
     *
     * @apiSuccessExample {json} Success Response:
     * {
     *   "success": true,
     *   "error": [],
     *   "data": {
     *     "id": 5,
     *     "ticket_number": "FITOUT-1005",
     *     "contractor_name": "XYZ Builders",
     *     "building": {"id": 2, "name": "Palm Heights"},
     *     "flat": {"id": 7, "property_number": "C-204"},
     *     "status": "rejected",
     *     "remarks": "Missing undertaking document"
     *   },
     *   "message": "Fit-Out form record fetched successfully."
     * }
     */
    public function show($id)
    {
        try {
            $fitout = FitOutForm::with(['building:id,name', 'flat:id,property_number'])->find($id);

            if (!$fitout) {
                return response()->json([
                    'success' => false,
                    'error' => 'Record not found.',
                    'data' => null,
                    'message' => 'Fit-Out not found.'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'error' => [],
                'data' => new FitOutFormResource($fitout),
                'message' => 'Fit-Out form record fetched successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('### Api ## FitOutFormApiController@show ## ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => null,
                'message' => 'Something went wrong.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
