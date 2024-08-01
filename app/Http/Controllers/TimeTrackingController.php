<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeTrackingController extends Controller
{
    public function startTracking(Request $request)
    {
        $timeLog = Activity::create([
            'user_id' => auth()->id(),
            'address_id' => $request->address_id,
            'starting_time' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        return response()->json($timeLog);
    }

    public function pauseTracking(Request $request, $id)
    {
        $timeLog = Activity::create([
            'user_id' => auth()->id(),
            'address_id' => $id,
            'activity_type' => 'break',
            'starting_time' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        return response()->json($timeLog);
    }

    public function resumeTracking(Request $request, $id)
    {
        $timeLog = Activity::find($id);
        $timeLog->update(['ending_time' => Carbon::now()]);

        return response()->json($timeLog);
    }

    public function stopTracking(Request $request, $id)
    {
        $timeLog = Activity::find($id);
        $timeLog->ending_time = Carbon::now()->format('Y-m-d H:i:s');
        $timeLog->save();

        $effectiveTime = $timeLog->calculateEffectiveTime();


        return response()->json($effectiveTime);
    }
}
