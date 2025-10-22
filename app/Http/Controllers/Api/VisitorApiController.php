<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\VisitorResource;
use App\Models\Visitor;

class VisitorApiController extends Controller
{
    /**
     * @api {get} /visitor/list List Visitors
     * @apiDescription
     * Returns a paginated list of visitors with optional filters.
     *
     * **Authentication Required:** Yes (Sanctum)
     *
     * @apiHeader {String} Authorization Bearer token required.
     * @apiHeader {String} Accept application/json
     *
     * @apiBody {Integer} [building_id] Optional. Filter by building.
     * @apiBody {Integer} [flat_id] Optional. Filter by flat.
     * @apiBody {String="all","pending","approved","rejected"} [status] Optional. Filter by status.
     * @apiBody {String="guest","delivery","maintenance"} [type] Optional. Filter by visitor type.
     *
     * @apiSuccessExample {json} Success Response:
     * {
     *   "success": true,
     *   "error": [],
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "John Doe",
     *       "type": "guest",
     *       "status": "approved",
     *       "remarks": "Verified entry",
     *       "building_id": 1,
     *       "flat_id": 2
     *     }
     *   ],
     *   "message": "Visitor list fetched successfully."
     * }
     */
    public function list(Request $request)
    {
        try {
            $query = Visitor::query()
                ->orderByDesc('created_at');

            // Filter by building or flat
            if ($request->filled('building_id')) {
                $query->where('building_id', $request->building_id);
            }

            if ($request->filled('flat_id')) {
                $query->where('flat_id', $request->flat_id);
            }

            // Filter by visitor type
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            // Optimized status filtering
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
                'data' => VisitorResource::collection($records),
                'message' => 'Visitor list fetched successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('### Api ## VisitorApiController@list ## ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => null,
                'message' => 'Something went wrong.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @api {get} /visitor/{id} Get Visitor Details
     * @apiDescription
     * Fetch detailed information about a specific visitor.
     *
     * @apiParam {Integer} id Visitor ID
     *
     * @apiSuccessExample {json} Success Response:
     * {
     *   "success": true,
     *   "error": [],
     *   "data": {
     *     "id": 5,
     *     "name": "Jane Smith",
     *     "type": "delivery",
     *     "status": "rejected",
     *     "remarks": "Unauthorized entry",
     *     "building_id": 2,
     *     "flat_id": 4
     *   },
     *   "message": "Visitor record fetched successfully."
     * }
     */
    public function show($id)
    {
        try {
            $visitor = Visitor::find($id);

            if (!$visitor) {
                return response()->json([
                    'success' => false,
                    'error' => 'Visitor not found.',
                    'data' => null,
                    'message' => 'Visitor record not found.'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'error' => [],
                'data' => new VisitorResource($visitor),
                'message' => 'Visitor record fetched successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('### Api ## VisitorApiController@show ## ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => null,
                'message' => 'Something went wrong.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
