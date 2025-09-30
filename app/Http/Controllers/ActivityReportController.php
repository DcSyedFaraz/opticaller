<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityReportController extends Controller
{
    /**
     * GET /api/activities
     *
     * Query params:
     * - date: YYYY-MM-DD (single day filter, optional)
     * - start_date: YYYY-MM-DD (range start, optional)
     * - end_date: YYYY-MM-DD (range end, optional)
     * - group_by_user: bool-like (0/1, true/false), default false
     * - user_id: int (optional, when not grouping or for a specific user's aggregates)
     */
    public function index(Request $request)
    {
        $tz = config('app.timezone', 'UTC');

        $date       = $request->query('date');
        $startInput = $request->query('start_date');
        $endInput   = $request->query('end_date');
        $userId     = $request->query('user_id');
        $groupBy    = filter_var($request->query('group_by_user', false), FILTER_VALIDATE_BOOLEAN);

        // Resolve date range
        if ($date) {
            // Validate simple YYYY-MM-DD
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return response()->json(['error' => 'Invalid date format. Use YYYY-MM-DD.'], 422);
            }
            $start = Carbon::parse($date, $tz)->startOfDay();
            $end   = Carbon::parse($date, $tz)->endOfDay();
        } elseif ($startInput && $endInput) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startInput) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endInput)) {
                return response()->json(['error' => 'Invalid start_date/end_date format. Use YYYY-MM-DD.'], 422);
            }
            $start = Carbon::parse($startInput, $tz)->startOfDay();
            $end   = Carbon::parse($endInput, $tz)->endOfDay();
        } else {
            // Default: today in app timezone
            $start = Carbon::now($tz)->startOfDay();
            $end   = Carbon::now($tz)->endOfDay();
        }

        if ($groupBy) {
            // Aggregated, user-wise view
            $query = DB::table('activities as a')
                ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
                ->whereBetween('a.created_at', [$start, $end])
                ->select([
                    'a.user_id',
                    DB::raw('COALESCE(u.name, CONCAT("User #", a.user_id)) as user_name'),
                    DB::raw('COUNT(*) as total_activities'),
                    DB::raw("SUM(CASE WHEN a.activity_type = 'call' THEN 1 ELSE 0 END) as call_count"),
                    DB::raw("SUM(CASE WHEN a.activity_type = 'break' THEN 1 ELSE 0 END) as break_count"),
                    DB::raw('SUM(a.call_duration) as total_call_duration'),
                ])
                ->groupBy('a.user_id', 'u.name')
                ->orderBy('user_name');

            if ($userId) {
                $query->where('a.user_id', (int) $userId);
            }

            $data = $query->get();

            return response()->json([
                'grouped' => true,
                'date_range' => [
                    'start' => $start->toAtomString(),
                    'end'   => $end->toAtomString(),
                ],
                'data' => $data,
            ]);
        }

        // Flat list view
        $activities = Activity::with(['users:id,name'])
            ->when($userId, fn ($q) => $q->where('user_id', (int) $userId))
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->get([
                'id',
                'user_id',
                'address_id',
                'activity_type',
                'feedback',
                'project',
                'call_duration',
                'created_at',
                'updated_at',
            ])
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'user_id' => $a->user_id,
                    'user_name' => optional($a->users)->name,
                    'address_id' => $a->address_id,
                    'activity_type' => $a->activity_type,
                    'feedback' => $a->feedback,
                    'project' => $a->project,
                    'call_duration' => $a->call_duration,
                    'created_at' => $a->created_at,
                    'updated_at' => $a->updated_at,
                ];
            });

        return response()->json([
            'grouped' => false,
            'date_range' => [
                'start' => $start->toAtomString(),
                'end'   => $end->toAtomString(),
            ],
            'count' => $activities->count(),
            'data' => $activities,
        ]);
    }
}

