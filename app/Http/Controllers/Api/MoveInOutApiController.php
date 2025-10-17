<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Forms\MoveInOut;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\MoveInOutResource;

class MoveInOutApiController extends Controller
{
    /**
     * @api {post} /move-in-out/list List Move-In Records
     * @apiDescription
     * Returns a filtered list of move-in records (same as Filament table logic).
     *  
     * **Authentication Required:** Yes (Sanctum)
     *
     * @apiHeader {String} Authorization Bearer token required.
     * @apiHeader {String} Accept application/json
     *
     * @apiBody {Integer} [building_id] Optional. Filter by building. not required for now
     * @apiBody {Integer} [flat_id] Optional. Filter by flat.
     * @apiBody {String="approved","rejected","NA"} [status] Optional. Filter by status.
     *
     * @apiSuccessExample {json} Success Response:
     * {
     *   "success": true,
     *   "error": [],
     *   "data": [
     *     {
     *       "id": 1,
     *       "ticket_number": "T-1001",
     *       "name": "John Doe",
     *       "building": {"id": 1, "name": "Tower A"},
     *       "flat": {"id": 2, "property_number": "A-201"},
     *       "status": "approved",
     *       "remarks": "Approved for 20th Oct move-in"
     *     }
     *   ],
     *   "message": "Move-in list fetched successfully."
     * }
     */
    public function list(Request $request)
    {
        try {
            $query = MoveInOut::query()
                ->where('type', 'move-in')
                ->with(['building:id,name', 'flat:id,property_number'])
                ->orderByDesc('created_at');

            // Apply filters
            // if ($request->filled('building_id')) {
            //     $flatIds = Flat::where('building_id', $request->building_id)->pluck('id');
            //     $query->whereIn('flat_id', $flatIds);
            // }

            if ($request->filled('flat_id')) {
                $query->where('flat_id', $request->flat_id);
            }

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
                'data' => MoveInOutResource::collection($records),
                'message' => 'Move-in list fetched successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('### Api ## MoveInOutController@list ## ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => null,
                'message' => 'Something went wrong.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @api {get} /move-in-out/{movein} Get Move-In/Out Details
     * @apiDescription
     * Fetch detailed information about a specific Move-In record.
     *
     * @apiParam {Integer} movein MoveInOut ID
     *
     * @apiSuccessExample {json} Success Response:
     * {
     *   "success": true,
     *   "error": [],
     *   "data": {
     *     "id": 5,
     *     "ticket_number": "T-1005",
     *     "name": "Jane Doe",
     *     "building": {"id": 1, "name": "Tower A"},
     *     "flat": {"id": 2, "property_number": "A-204"},
     *     "status": "rejected",
     *     "remarks": "Documents missing"
     *   },
     *   "message": "Move-in record fetched successfully."
     * }
     */
    public function show(MoveInOut $movein)
    {
        try {
            $movein->load(['building:id,name', 'flat:id,property_number']);

            return response()->json([
                'success' => true,
                'error' => [],
                'data' => new MoveInOutResource($movein),
                'message' => 'Move-in record fetched successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('### Api ## MoveInOutController@show ## ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => null,
                'message' => 'Something went wrong.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
