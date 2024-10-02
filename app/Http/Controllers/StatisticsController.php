<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Address;
use App\Models\LoginTime;
use App\Models\NotReached;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        // Retrieve date range from request or set default
        $startDate = $request->input('startDate') ? Carbon::parse($request->input('startDate')) : Carbon::now()->subHours(24);
        $endDate = $request->input('endDate') ? Carbon::parse($request->input('endDate'))->endOfDay() : Carbon::now();

        // Fetch users with login times and activities within the specified date range
        $users = User::with([
            'loginTimess' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('login_time', [$startDate, $endDate]);
            },
            'activities' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        ])->get();

        $userData = $users->map(function ($user) {
            $totalLoggedInTime = $user->loginTimess->reduce(function ($carry, $loginTime) {
                $loginDuration = strtotime($loginTime->logout_time) - strtotime($loginTime->login_time);
                return $carry + max($loginDuration, 0);
            }, 0);

            $activities = $user->activities;
            $totalBreakTime = $activities->where('activity_type', 'break')->sum('total_duration');
            $callActivities = $activities->where('activity_type', 'call');
            $addressesProcessed = $callActivities->count();
            $averageProcessingTime = $callActivities->avg('total_duration') ?? 0;
            $totalEffectiveWorkingTime = $totalLoggedInTime - $totalBreakTime - $callActivities->sum('total_duration');

            return [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'total_logged_in_time' => $totalLoggedInTime,
                'total_break_time' => $totalBreakTime,
                'addresses_processed' => $addressesProcessed,
                'average_processing_time' => $averageProcessingTime,
                'total_effective_working_time' => $totalEffectiveWorkingTime,
            ];
        });

        // Calculate daily call-out volume
        $dailyCallOutVolume = Activity::where('activity_type', 'call')
            ->when($request->input('startDate'), function ($query, $startDate) use ($request) {
                $endDate = $request->input('endDate');
                return $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        // Calculate aggregated statistics
        $avgCall = Activity::where('activity_type', 'call')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->avg('total_duration');
        $totalBreak = Activity::where('activity_type', 'break')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_duration');

        // Fetch all login times for total calculation
        $totalLoggedInTime = LoginTime::whereBetween('login_time', [$startDate, $endDate])
            ->whereNotNull('logout_time')
            ->get()
            ->reduce(function ($carry, $loginTime) {
                $loginDuration = Carbon::parse($loginTime->logout_time)->diffInSeconds(Carbon::parse($loginTime->login_time));
                return $carry + $loginDuration;
            }, 0);

        $totalLoggedInTimeFormatted = $totalLoggedInTime;

        // Count addresses and feedbacks within the date range
        $updatedAddressesToday = Address::whereBetween('updated_at', [$startDate, $endDate])->count();
        $notInterestedCountToday = Address::where('feedback', 'Not Interested')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();
        $followCountToday = Address::where('feedback', 'Follow-up')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();
        $successfulOutcomes = Address::where('feedback', 'Interested')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();
        $notReachedCountToday = NotReached::whereBetween('updated_at', [$startDate, $endDate])->count();

        // Calculate successful calls today and success rate
        $successfulCallsToday = $updatedAddressesToday - ($notInterestedCountToday + $notReachedCountToday);
        $successfulPercentage = $updatedAddressesToday > 0 ? ($successfulCallsToday / $updatedAddressesToday) * 100 : 0;

        $employeeLeaderboard = $userData->sortByDesc('addresses_processed')->first()['user_name'] ?? null;

        $totalAddressesWithNullFeedback = Address::whereNull('feedback')->count();

        // Counts per project
        $projectsWithAddressCounts = Project::withCount([
            'addresses' => function ($query) {
                $query->whereNull('feedback');
            }
        ])->get();

        $data = [
            'dailyCallOutVolume' => $dailyCallOutVolume,
            'successRateData' => $successfulPercentage,
            'employeeLeaderboard' => $employeeLeaderboard,
            'addressProces' => $updatedAddressesToday,
            'followCountToday' => $followCountToday,
            'successfulOutcomes' => $successfulOutcomes,
            'total_logged_in_time' => $totalLoggedInTimeFormatted,
            'avgCall' => $avgCall,
            'totalBreak' => $totalBreak,
            'totalAddressesWithNullFeedback' => $totalAddressesWithNullFeedback,
            'projectsWithAddressCounts' => $projectsWithAddressCounts,
        ];

        // dd($data, $userData);
        return Inertia::render('Stats/index', [
            'userData' => $userData,
            'data' => $data,
        ]);
    }
    public function dashboard(Request $request)
    {
        $user = auth()->user(); // Get the authenticated user

        // Retrieve date range from request or set default
        $startDate = $request->input('startDate') ? Carbon::parse($request->input('startDate')) : Carbon::now()->subHours(24);
        $endDate = $request->input('endDate') ? Carbon::parse($request->input('endDate'))->endOfDay() : Carbon::now();

        // Today's Call-Out Count for the authenticated user
        $todaysCallOutCount = Activity::where('activity_type', 'call')
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Today's Completed Addresses for the authenticated user
        $todaysCompletedAddresses = Activity::where('activity_type', 'call')
            ->where('user_id', $user->id)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        // Average weekly address processing time for the last 7 days
        $last7Days = Carbon::now()->subDays(7);
        $averageProcessingTime = Activity::where('activity_type', 'call')
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$last7Days, Carbon::now()])
            ->avg('total_duration') ?? 0;

        // Average weekly address processing time for the last 7 days (for graph)
        $weeklyProcessingTimes = Activity::where('activity_type', 'call')
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$last7Days, Carbon::now()])
            ->get(['created_at', 'total_duration']);

        $processingTimeGraphData = $weeklyProcessingTimes->groupBy(function ($date) {
            return Carbon::parse($date->created_at)->format('Y-m-d'); // grouping by dates
        })->map(function ($row) {
            return $row->avg('total_duration');
        });


        // Today's Break Times
        $todaysBreakTime = Activity::where('activity_type', 'break')
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_duration');

        // Break Time Data for the last 7 days (for graph)
        $last7DaysBreakTimes = Activity::where('activity_type', 'break')
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$last7Days, Carbon::now()])
            ->get(['created_at', 'total_duration']);

        $breakTimeGraphData = $last7DaysBreakTimes->groupBy(function ($date) {
            return Carbon::parse($date->created_at)->format('Y-m-d'); // grouping by dates
        })->map(function ($row) {
            return $row->sum('total_duration');
        });

        // Yesterday's Working Hours
        $yesterdayStart = Carbon::yesterday()->startOfDay();
        $yesterdayEnd = Carbon::yesterday()->endOfDay();
        $yesterdayWorkingHours = LoginTime::where('user_id', $user->id)
            ->whereBetween('login_time', [$yesterdayStart, $yesterdayEnd])
            ->whereNotNull('logout_time')
            ->get()
            ->reduce(function ($carry, $loginTime) {
                $loginDuration = Carbon::parse($loginTime->logout_time)->diffInSeconds(Carbon::parse($loginTime->login_time));
                return $carry + $loginDuration;
            }, 0);

        // Call Volume Data for the last 7 days (for graph)
        $callVolumeGraphData = Activity::where('activity_type', 'call')
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$last7Days, Carbon::now()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        // Today's Success Rate
        $subProjectIds = $user->subProjects()->pluck('sub_project_id');
        $successfulCalls = Address::whereIn('sub_project_id', $subProjectIds)
            ->where('feedback', 'Interested')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();
        $notReachedCalls = Address::whereIn('sub_project_id', $subProjectIds)
            ->where('feedback', 'Not Interested')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        $totalCalls = $successfulCalls + $notReachedCalls;
        $successRate = $totalCalls > 0 ? ($successfulCalls / $totalCalls) * 100 : 0;

        // Preparing data for the frontend
        $data = [
            'todaysCallOutCount' => $todaysCallOutCount,
            'todaysCompletedAddresses' => $todaysCompletedAddresses,
            'averageProcessingTime' => $averageProcessingTime,
            'todaysBreakTime' => $todaysBreakTime,
            'breakTimeGraphData' => $breakTimeGraphData,
            'yesterdayWorkingHours' => $yesterdayWorkingHours,
            'callVolumeGraphData' => $callVolumeGraphData,
            'successRate' => $successRate,
            'processingTimeGraphData' => $processingTimeGraphData,

        ];

        return Inertia::render('Dashboard', [
            'data' => $data,
        ]);
    }



}
