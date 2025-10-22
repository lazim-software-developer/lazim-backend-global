<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\GuestResource;
use App\Models\Forms\Guest;

class GuestApiController extends Controller
{
    /**
     * @api {get} /guest/list List Guests
     * @apiDescription
     * Returns a paginated list of guests with optional filters.
     *
     * **Authentication Required:** Yes (Sanctum)
     *
     * @apiHeader {String} Authorization Bearer token required.
     * @apiHeader {String} Accept application/json
     *
     * @apiBody {Integer} [building_id] Optional. Filter by building.
     * @apiBody {Integer} [flat_id] Optional. Filter by flat.
     * @apiBody {String="all","pending","approved","rejected"} [status] Optional. Filter by approval status.
     * @apiBody {String} [guest_name] Optional. Search by guest name.
     *
     * @apiSuccessExample {json} Success Response:
     * {
     *   "success": true,
     *   "error": [],
     *   "data": [
     *     {
     *       "id": 1,
     *       "guest_name": "John Doe",
     *       "holiday_home_name": "Palm View",
     *       "status": "approved",
     *       "stay_duration": "7 days"
     *     }
     *   ],
     *   "message": "Guest list fetched successfully."
     * }
     */
    public function list(Request $request)
    {
        try {
            $query = Guest::query()
                ->with(['building:id,name', 'flat:id,property_number'])
                ->orderByDesc('created_at');

            if ($request->filled('building_id')) {
                $query->where('building_id', $request->building_id);
            }

            if ($request->filled('flat_id')) {
                $query->where('flat_id', $request->flat_id);
            }

            if ($request->filled('guest_name')) {
                $query->where('guest_name', 'like', '%' . $request->guest_name . '%');
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
                'data' => GuestResource::collection($records),
                'message' => 'Guest list fetched successfully.'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error('### Api ## GuestApiController@list ## ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => null,
                'message' => 'Something went wrong.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @api {get} /guest/{id} Get Guest Details
     * @apiDescription
     * Fetch detailed information about a specific guest record.
     *
     * @apiParam {Integer} id Guest ID
     *
     * @apiSuccessExample {json} Success Response:
     * {
     *   "success": true,
     *   "error": [],
     *   "data": {
     *     "id": 5,
     *     "guest_name": "Jane Smith",
     *     "passport_number": "A1234567",
     *     "status": "pending",
     *     "building": {
     *        "id": 1,
     *        "name": "Sunset Tower"
     *     },
     *     "flat": {
     *        "id": 3,
     *        "property_number": "A-305"
     *     }
     *   },
     *   "message": "Guest record fetched successfully."
     * }
     */
    public function show($id)
    {
        try {
             $guest = Guest::with(['building:id,name', 'flat:id,property_number'])->find($id);

            if (!$guest) {
                return response()->json([
                    'success' => false,
                    'error' => 'Record not found.',
                    'data' => null,
                    'message' => 'Guest record not found.'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'error' => [],
                'data' => new GuestResource($guest),
                'message' => 'Guest record fetched successfully.'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error('### Api ## GuestApiController@show ## ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => null,
                'message' => 'Something went wrong.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
