<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use App\Enums\ReviewType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
use App\Http\Resources\ReviewResource;


class ReviewController extends Controller
{
    /**
     * @api {post} /review/create Create a new review
     * @apiDescription
     * Allows an authenticated user to create a review.
     *  
     * The review includes a comment, a feedback rating, and a type (currently only "feedback" is supported).
     *  
     * **Authentication Required:** Yes (Sanctum)
     *
     * @apiHeader {String} Authorization Bearer token required.
     * @apiHeader {String} Accept application/json
     *
     * @apiBody {Integer} oa_id   Required. Must exist in `owner_associations` table.
     * @apiBody {Integer} flat_id Required. Must exist in `flats` table.
     * @apiBody {String="feedback"} type Required. Type of review (only `"feedback"` currently allowed).
     * @apiBody {String} [comment] Optional. A written remark about the flat or service.
     * @apiBody {Integer=1,2,3} feedback Required. Numeric rating where:
     * - **1** = Good  
     * - **2** = Average  
     * - **3** = Bad
     *
     * @apiExample {json} Example Request:
     * {
     *   "oa_id": 1,
     *   "flat_id": 2,
     *   "type": "feedback",
     *   "comment": "The flat is well maintained and clean.",
     *   "feedback": 1
     * }
     *
     * @apiSuccess (200 OK) {Boolean} success Whether the operation succeeded.
     * @apiSuccess (200 OK) {Array} error Empty array if no errors.
     * @apiSuccess (200 OK) {Object} data Newly created review resource.
     * @apiSuccess (200 OK) {String} message Success message.
     *
     * @apiSuccessExample {json} Success Response:
     * {
     *   "success": true,
     *   "error": [],
     *   "data": {
     *     "id": 15,
     *     "user_id": 5,
     *     "oa_id": 1,
     *     "flat_id": 2,
     *     "type": "feedback",
     *     "comment": "The flat is well maintained and clean.",
     *     "feedback": 1,
     *     "created_at": "2025-10-15T09:40:18.000000Z"
     *   },
     *   "message": "Review created successfully"
     * }
     *
     * @apiError (422 Unprocessable Entity) ValidationError Returned when validation fails.
     * @apiErrorExample {json} Validation Error Response:
     * {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "oa_id": ["The selected owner association does not exist."],
     *     "flat_id": ["The selected flat does not exist."],
     *     "feedback": ["Feedback must be 1 (good), 2 (average), or 3 (bad)."]
     *   }
     * }
     *
     * @apiError (500 Internal Server Error) ServerError Returned when an exception occurs.
     * @apiErrorExample {json} Server Error Response:
     * {
     *   "success": false,
     *   "error": "SQLSTATE[23000]: Integrity constraint violation...",
     *   "data": null,
     *   "message": "Something went wrong."
     * }
     */

    public function store(ReviewRequest $request)
    {
        try {
            $review = Review::create([
                'user_id'  => auth()->id(), 
                'oa_id'    => $request->oa_id,
                'flat_id'  => $request->flat_id,
                'type'     => ReviewType::fromLabel($request->type), 
                'comment'  => $request->comment,
                'feedback' => $request->feedback,
            ]);

            return response()->json(['success' => true, 'error' => [], 'data' => new ReviewResource($review), 'message' => 'Review created successfully'], RESPONSE::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('### Api ## ReviewController@store ## ' . $e->getMessage());
            return response()->json(['success' => false,'error'   => $e->getMessage(),'data'    => null,'message' => 'Something went wrong.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
