<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CallReportController extends Controller
{
    /**
     * Return call activity records for a given date in Europe/Berlin time as CSV.
     * Defaults to 2024-10-24 if no date is provided.
     *
     * CSV columns: user_name, called_number, address_id, activity_type, feedback,
     * call_duration, created_at_berlin, updated_at_berlin
     */
    public function daily(Request $request)
    {
        $date = $request->input('date', '2024-10-24');

        // Validate basic date format (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json(['error' => 'Invalid date format. Use YYYY-MM-DD.'], 422);
        }

        $tz = 'Europe/Berlin';

        // Compute the Berlin local day window
        $start = Carbon::parse($date, $tz)->startOfDay();
        $end   = Carbon::parse($date, $tz)->endOfDay();

        // Query activities joined with users and addresses
        $rows = DB::table('activities as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->leftJoin('addresses as addr', 'addr.id', '=', 'a.address_id')
            ->where('a.activity_type', 'call')
            ->whereBetween('a.created_at', [$start, $end])
            ->orderBy('a.created_at', 'asc')
            ->select([
                DB::raw('COALESCE(u.name, CONCAT("User #", a.user_id)) as user_name'),
                DB::raw('addr.phone_number as called_number'),
                'a.address_id',
                'a.activity_type',
                'a.feedback',
                'a.call_duration',
                'a.created_at',
                'a.updated_at',
            ])
            ->get();

        $filename = sprintf('call_report_%s.csv', $date);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($rows, $tz) {
            $out = fopen('php://output', 'w');
            // Header row
            fputcsv($out, [
                'user_name',
                'called_number',
                'address_id',
                'activity_type',
                'feedback',
                'call_duration',
                'created_at_berlin',
                'updated_at_berlin',
            ]);

            foreach ($rows as $r) {
                $created = $r->created_at ? Carbon::parse($r->created_at)->setTimezone($tz)->format('Y-m-d H:i:s') : null;
                $updated = $r->updated_at ? Carbon::parse($r->updated_at)->setTimezone($tz)->format('Y-m-d H:i:s') : null;
                fputcsv($out, [
                    $r->user_name,
                    $r->called_number,
                    $r->address_id,
                    $r->activity_type,
                    $r->feedback,
                    $r->call_duration,
                    $created,
                    $updated,
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
