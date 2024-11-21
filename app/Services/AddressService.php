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
                ->where(function ($query) {
                    $query->whereNull('addresses.seen')
                        ->orWhere('addresses.seen', '<', Carbon::now()->subDay());
                })
                ->where(function ($query) use ($now) {
                    $query->where('follow_up_date', '<=', $now)
                        ->orWhere('follow_up_date', '=', $now);
                })
                ->orderBy('follow_up_date', 'asc')
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
                ->where(function ($query) {
                    $query->whereNull('addresses.seen')
                        ->orWhere('addresses.seen', '<', Carbon::now()->subDay());
                })
                ->whereNull('follow_up_date')
                ->where(function ($query) use ($now) {
                    $query->whereHas('notreached', function ($q) use ($now) {
                        $q->where(function ($subQuery) use ($now) {
                            $subQuery->whereNull('paused_until')
                                ->orWhere('paused_until', '<=', $now);
                        })->where('attempt_count', '<=', 9);
                    })
                        ->orWhereDoesntHave('notreached');
                })
                ->select('addresses.*')
                ->lockForUpdate() // Lock the selected rows for update
                ->paginate($addressesPerPage);

            $address = $addresses->first();

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

