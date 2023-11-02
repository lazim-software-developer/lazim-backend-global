<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppFeedbackRequest;
use App\Models\AppFeedback;

class AppFeedbackController extends Controller
{
    public function store(StoreAppFeedbackRequest $request)
    {
        $feedback = AppFeedback::create($request->validated() + ['user_id' => auth()->user()->id]);

        return response()->json(['message' => 'Feedback submitted successfully', 'feedback' => $feedback], 201);
    }
}
