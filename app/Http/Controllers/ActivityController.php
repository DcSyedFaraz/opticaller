<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function store(Request $request)
    {
        $activity = Activity::create([
            'user_id' => auth()->id(),
            'address_id' => $request->address_id,
            'project_id' => $request->project_id,
            'activity_type' => $request->activity_type,
            'start_time' => now(),
        ]);

        return response()->json($activity);
    }

    public function update(Request $request, Activity $activity)
    {
        $activity->update(['end_time' => now()]);
        return response()->json($activity);
    }
}
