<?php
namespace App\Services;

use App\Models\Address;
use App\Models\SubProject;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Log;

class AddressService
{
    public function getDueAddress()
    {
        $now = Carbon::now();

        $threshold = now()->subMinutes(1);

        // Check for stale addresses as before
        $staleAddresses = Address::whereHas('latestStatus', function ($query) use ($threshold) {
            $query->where('updated_at', '<', $threshold)
                ->where('status', '!=', 'Finished');
        })->get();

        if ($staleAddresses->isNotEmpty()) {
            Log::channel('address')->warning('Webhook Not Working.', [
                'timestamp' => $now->toDateTimeString(),
                'addresses' => $staleAddresses,
            ]);

            return response()->json(['warning' => 'Oops! The system is having trouble connecting. Please contact admin as the webhook is not working properly.'], 404);
        }

        // Start a database transaction
        return DB::transaction(function () use ($now) {
            $subProjectIds = auth()->user()->subProjects()->pluck('sub_project_id');

            // Try to fetch a due address with locking
            $dueAddress = Address::with('calLogs.notes', 'subproject.projects', 'subproject.feedbacks', 'calLogs.users')
                ->whereIn('sub_project_id', $subProjectIds)
                ->where('forbidden_promotion', false)
                ->where(function ($query) {
                    $query->whereNull('addresses.seen')
                        ->orWhere('addresses.seen', '<', Carbon::now()->subMinutes(15));
                })
                ->where(function ($query) use ($now) {
                    $query->where('follow_up_date', '<=', $now)
                        ->orWhere('follow_up_date', '=', $now)
                        ->orWhere('re_call_date', '<=', $now)
                        ->orWhere('re_call_date', '=', $now);
                })
                ->orderByRaw('COALESCE(follow_up_date, re_call_date) ASC')
                ->lockForUpdate() // Lock the selected rows for update
                ->first();

            if ($dueAddress) {
                $dueAddress->seen = $now;
                $dueAddress->save();

                Log::channel('address')->info('Due Address Processed', [
                    'seen' => $dueAddress->seen,
                    'user_id' => auth()->id(),
                    'address' => $dueAddress->toArray(),
                ]);

                return $dueAddress;
            }

            // If no due address, fetch addresses dynamically
            $addressesPerPage = 1;

            $subProjectIds = auth()->user()->subProjects()->pluck('sub_project_id');

            $addresses = Address::with('calLogs.notes', 'subproject.projects', 'subproject.fieldVisibilities', 'subproject.feedbacks', 'calLogs.users', 'project')
                ->join('sub_projects', 'addresses.sub_project_id', '=', 'sub_projects.id')
                ->orderBy('sub_projects.priority', 'desc')
                ->whereIn('sub_project_id', $subProjectIds)
                ->where('forbidden_promotion', false)
                ->where(function ($query) {
                    $query->whereNull('addresses.seen')
                        ->orWhere('addresses.seen', '<', Carbon::now()->subMinutes(15));
                })
                ->whereNull('follow_up_date')
                ->whereNull('re_call_date')
                ->where(function ($query) use ($now) {
                    $query->whereHas('notreached', function ($q) use ($now) {
                        $q->where(function ($subQuery) use ($now) {
                            $subQuery->whereNull('paused_until')
                                ->orWhere('paused_until', '<=', $now);
                        });
                    })
                        ->orWhereDoesntHave('notreached');
                })
                ->select('addresses.*')
                ->lockForUpdate() // Lock the selected rows for update
                ->paginate($addressesPerPage);

            $address = $addresses->first();

            // Filter out addresses that have exceeded their retry attempts
            if ($address && $address->notreached()->exists()) {
                $latestNotReached = $address->notreached()->latest()->first();
                $subProject = $address->subproject;

                if ($subProject && !$subProject->hasMoreRetryAttempts($latestNotReached->attempt_count)) {
                    // This address has exceeded its retry attempts, skip it
                    Log::channel('address')->info('Address exceeded retry attempts, skipping', [
                        'address_id' => $address->id,
                        'attempt_count' => $latestNotReached->attempt_count,
                        'sub_project_id' => $subProject->id,
                    ]);

                    // Try to get the next address
                    $addresses = $addresses->slice(1);
                    $address = $addresses->first();
                }
            }

            if (!$address) {
                Log::channel('address')->warning('No Addresses to Process', [
                    'timestamp' => $now->toDateTimeString(),
                    'user_id' => auth()->id(),
                ]);

                return response()->json(['message' => 'No more addresses to process'], 404);
            }
            $seen = $address->seen;
            $address->seen = $now;
            $address->save();

            Log::channel('address')->info('Address Processed', [
                'seen' => $seen,
                'user_id' => auth()->id(),
                'address' => $address->toArray(),
            ]);

            return $address;
        });
    }
}

