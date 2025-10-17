<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\SaleNOCResource;
use App\Models\Forms\SaleNOC;

class SaleNOCApiController extends Controller
{
    /**
     * @api {post} /sale-noc/list List Sale NOC Records
     * @apiDescription
     * Returns a paginated list of Sale NOC records with optional filters.
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
     *       "ticket_number": "NOC-1001",
     *       "applicant": "John Doe",
     *       "sale_price": "4500000",
     *       "building": {"id": 1, "name": "Skyline Towers"},
     *       "flat": {"id": 3, "property_number": "B-301"},
     *       "status": "approved",
     *       "remarks": "Verified successfully"
     *     }
     *   ],
     *   "message": "Sale NOC list fetched successfully."
     * }
     */
    public function list(Request $request)
    {
        try {
            $query = SaleNOC::query()
                ->with(['building:id,name', 'flat:id,property_number'])
                ->orderByDesc('created_at');

            // Apply filters
            if ($request->filled('building_id')) {
                $query->where('building_id', $request->building_id);
            }

            if ($request->filled('flat_id')) {
                $query->where('flat_id', $request->flat_id);
            }

            // Status filter (optimized)
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
                'data' => SaleNOCResource::collection($records),
                'message' => 'Sale NOC list fetched successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('### Api ## SaleNOCApiController@list ## ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => null,
                'message' => 'Something went wrong.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @api {get} /sale-noc/{saleNoc} Get Sale NOC Details
     * @apiDescription
     * Fetch detailed information about a specific Sale NOC record.
     *
     * @apiParam {Integer} saleNoc Sale NOC ID
     *
     * @apiSuccessExample {json} Success Response:
     * {
     *   "success": true,
     *   "error": [],
     *   "data": {
     *     "id": 5,
     *     "ticket_number": "NOC-1005",
     *     "applicant": "Jane Doe",
     *     "sale_price": "5500000",
     *     "building": {"id": 2, "name": "Sunshine Residency"},
     *     "flat": {"id": 10, "property_number": "C-102"},
     *     "status": "rejected",
     *     "remarks": "Payment pending"
     *   },
     *   "message": "Sale NOC record fetched successfully."
     * }
     */
    public function show(SaleNOC $saleNoc)
    {
        try {
            $saleNoc->load(['building:id,name', 'flat:id,property_number']);

            return response()->json([
                'success' => true,
                'error' => [],
                'data' => new SaleNOCResource($saleNoc),
                'message' => 'Sale NOC record fetched successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('### Api ## SaleNOCApiController@show ## ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => null,
                'message' => 'Something went wrong.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
