<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Resources\Community\PollResource;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\Building;
use App\Models\Community\Poll;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function index(Building $building)
    {
        $count = request('count', 10);
        $polls = Poll::with(['responses' => function ($query) {
            $query->where('submitted_by', auth()->id());
        }])
            ->where('building_id', $building->id)
            ->where('status', 'published')
            ->where('active',true)
            ->where(function ($query) {
                $query->where('scheduled_at', '<', now())->where('ends_on', '>', now())
                    ->orWhereNull('ends_on');
            })->latest()->paginate($count);

        return PollResource::collection($polls);
    }

    public function store(Request $request, Poll $poll)
    {
        // Check if the user has already submitted a response for this poll
        $existingResponse = $poll->responses()->where('submitted_by', auth()->id())->first();

        if ($existingResponse) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'You have already submitted a response for this poll.',
                'code' => 422,
            ]))->response()->setStatusCode(422);
        }

        // Create a new response
        $response = $poll->responses()->create([
            'answer' => $request->input('answer'),
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
        ]);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Submitted successfully!',
            'code' => 200,
        ]))->response()->setStatusCode(200);
    }
}
