<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppFeedbackRequest;
use App\Models\AppFeedback;
use Illuminate\Support\Facades\DB;

class AppFeedbackController extends Controller
{
    public function store(StoreAppFeedbackRequest $request)
    {
        if($request->has('building_id')){
            $oa_id = DB::table('building_owner_association')->where('building_id', $request->building_id)->where('active', true)->first()->owner_association_id;
        }

        $feedback = AppFeedback::create($request->validated() + ['user_id' => auth()->user()->id]);

        return response()->json(['message' => 'Feedback submitted successfully', 'feedback' => $feedback], 201);
    }
}
