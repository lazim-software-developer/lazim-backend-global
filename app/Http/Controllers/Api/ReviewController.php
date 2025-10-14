<?php

namespace App\Http\Controllers\Api;

use App\Enums\ReviewType;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ReviewController extends Controller
{
    /**
     * Store a newly created review.
     */
    public function store(ReviewRequest $request)
    {
        try {
            $review = Review::create([
                'user_id' => auth()->id(),
                'oa_id' => $request->oa_id,
                'flat_id' => $request->flat_id,
                'type' => ReviewType::fromLabel($request->type),
                'comment' => $request->comment,
                'feedback' => $request->feedback,
            ]);

            return response()->json(['success' => true, 'error' => [], 'data' => new ReviewResource($review), 'message' => 'Review created successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('### Api ## ReviewController@store ## ' . $e->getMessage());

            return response()->json(['success' => false, 'error' => $e->getMessage(), 'data' => null, 'message' => 'Something went wrong.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
