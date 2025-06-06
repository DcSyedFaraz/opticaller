<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Address;
use App\Models\LoginTime;
use App\Models\NotReached;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        // Retrieve date range from request or set default
        $inputStartDate = $request->input('startDate');
        $inputEndDate = $request->input('endDate');
        $timezone = config('app.timezone');

        // Initialize startDate and endDate
        if ($inputStartDate && $inputEndDate) {
            // Parse startDate
            $startDate = Carbon::createFromFormat('Y-m-d', $inputStartDate, $timezone)->startOfDay();

            // Parse endDate
            $endDate = Carbon::createFromFormat('Y-m-d', $inputEndDate, $timezone)->endOfDay();

        } else {
            // Default to the last 24 hours if no dates are provided
            $startDate = Carbon::now($timezone)->subHours(24);
            $endDate = Carbon::now($timezone);
        }


        // Debugging: Uncomment to check the dates
// dd($startDate, $endDate);
        // dd($startDate,$endDate,$request->all(),$timezone);
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
        $notInterestedCountToday = Address::whereHas('feedbacks', function ($query) {
            $query->where('no_statistics', true);
        })
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();
        $followCountToday = Address::whereNotNull('follow_up_date')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();
        $successfulOutcomes = Address::whereBetween('updated_at', [$startDate, $endDate])
            ->whereHas('feedbacks', function ($query) {
                $query->where('no_statistics', false);
            })
            ->count();
        $notReachedCountToday = NotReached::whereBetween('updated_at', [$startDate, $endDate])->count();

        // Calculate successful calls today and success rate
        $successfulCallsToday = $updatedAddressesToday - ($notInterestedCountToday + $notReachedCountToday);
        $successfulPercentage = $updatedAddressesToday > 0 ? ($successfulCallsToday / $updatedAddressesToday) * 100 : 0;

        $employeeLeaderboard = $userData->sortByDesc('addresses_processed')->first()['user_name'] ?? null;

        $totalAddressesWithNullFeedback = Address::whereNull('feedback')->count();

        $feedbackCounts = Activity::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('feedback')
            ->select('user_id', 'feedback', DB::raw('count(*) as total'))
            ->groupBy('user_id', 'feedback')
            ->where('feedback', '!=', '')
            ->with('users')
            ->get()
            ->groupBy('user_id')
            ->map(function ($feedbacks) {
                return $feedbacks->mapWithKeys(function ($item) {
                    return [$item->feedback => $item->total];
                });
            });
        $userData = $userData->map(function ($user) use ($feedbackCounts) {
            $userFeedbacks = $feedbackCounts->get($user['user_id'], []);
            return array_merge($user, ['feedback_counts' => $userFeedbacks]);
        });

        // dd($userData);
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
        $user = auth()->user(); // Get the authenticated
        $usersWithCalls = null;

        if ($user->hasRole('admin')) {
            $usersWithCalls = User::role('user')
                ->whereHas('callActivities')
                ->with(['lastCall:id,user_id,updated_at'])
                ->select('id', 'name')
                ->whereHas('lastCall')
                ->orderBy(
                    DB::table('activities')
                        ->select('updated_at')
                        ->whereColumn('user_id', 'users.id')
                        ->orderBy('updated_at', 'desc')
                        ->limit(1),
                    'desc'
                )
                ->get();
        }

        // Retrieve date range from request or set default
        $startDate = $request->input('startDate')
            ? Carbon::parse($request->input('startDate'))->startOfDay()
            : Carbon::today()->startOfDay();

        // Set endDate to the end of today if not provided
        $endDate = $request->input('endDate')
            ? Carbon::parse($request->input('endDate'))->endOfDay()
            : Carbon::today()->endOfDay();

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
        $yesterdayStartDate = Carbon::yesterday()->startOfDay();
        $yesterdayEndDate = Carbon::yesterday()->endOfDay();


        // Today's Break Times
        $todaysBreakTime = Activity::where('activity_type', 'break')
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$yesterdayStartDate, $yesterdayEndDate])
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


        $yesterdayWorkingHours = LoginTime::where('user_id', $user->id)
            ->whereBetween('login_time', [$yesterdayStartDate, $yesterdayEndDate])
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
            ->whereHas('feedbacks', function ($query) {
                $query->where('no_statistics', false);
            })
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();
        $notReachedCalls = Address::whereIn('sub_project_id', $subProjectIds)
            ->whereHas('feedbacks', function ($query) {
                $query->where('no_statistics', true);
            })
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
            'usersWithCalls' => $usersWithCalls,
        ]);
    }



}
