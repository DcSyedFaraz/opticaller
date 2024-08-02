<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Address;
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
        $timeLog->ending_time = Carbon::now()->format('Y-m-d H:i:s');
        $totalTime = $timeLog->ending_time->diffInSeconds($timeLog->starting_time);
        $timeLog->total_duration = $totalTime;
        $timeLog->save();

        return response()->json($timeLog);
    }

    public function stopTracking(Request $request, $id)
    {
        $timeLog = Activity::find($id);
        $timeLog->ending_time = Carbon::now()->format('Y-m-d H:i:s');
        $effectiveTime = $timeLog->calculateEffectiveTime();
        $timeLog->total_duration = $effectiveTime;
        $timeLog->save();



        $address = Address::inRandomOrder()->with('calLogs')->first();
        // dd($address->company_name);
        return response()->json(['address' => $address]);
        // return inertia('user/index', ['address' => $address]);
    }
}
