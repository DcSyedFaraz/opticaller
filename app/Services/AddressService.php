<?php
namespace App\Services;

use App\Models\Address;
use App\Models\SubProject;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Log;

class AddressService
{
    public function getDueAddress()
    {
        $now = Carbon::now();
        // $nowInUtc = Carbon::now('Europe/Berlin');
        // dd($now);
        // $now = $nowInUtc->copy()->setTimezone('UTC');

        // Convert $now to UTC for storing in the database and for comparison
        $subProjectIds = auth()->user()->subProjects()->pluck('sub_project_id');
        // Session::forget('addresses');

        $dueAddress = Address::with('calLogs.notes', 'subproject.projects', 'subproject.feedbacks', 'calLogs.users')
            ->whereIn('sub_project_id', $subProjectIds)
            ->where(function ($query) {
                $query->whereNull('addresses.seen')  // Checks if 'seen' is null (empty)
                    // ->orWhere('addresses.seen', '<', Carbon::now()->subMinutes(3));  // Checks if 'seen' is older than 24 hours
                    ->orWhere('addresses.seen', '<', Carbon::now()->subDay());  // Checks if 'seen' is older than 24 hours
            })
            ->where(function ($query) use ($now) {
                $query->where('follow_up_date', '<=', $now)
                    ->orWhere('follow_up_date', '=', $now);
            })
            ->orderBy('follow_up_date', 'asc')
            ->first();

        if ($dueAddress) {
            $dueAddress->seen = $now;
            $dueAddress->save();
            // dd($now,  $dueAddress->follow_up_date);

            Log::channel('address')->info('Due Address Processed', [
                'timestamp' => $now->toDateTimeString(),
                'user_id' => auth()->id(),
                'address' => $dueAddress->toArray(),
            ]);

            return $dueAddress;
        }


        $addressesPerPage = 1;

        // Fetch addresses dynamically
        $addresses = Address::with('calLogs.notes', 'subproject.projects', 'subproject.fieldVisibilities', 'subproject.feedbacks', 'calLogs.users', 'project')
            ->join('sub_projects', 'addresses.sub_project_id', '=', 'sub_projects.id') // Join sub_projects
            ->orderBy('sub_projects.priority', 'desc')
            ->whereIn('sub_project_id', $subProjectIds)
            ->where(function ($query) {
                $query->whereNull('addresses.seen')  // Checks if 'seen' is null (empty)
                    // ->orWhere('addresses.seen', '<', Carbon::now()->subMinutes(3));  // Checks if 'seen' is older than 24 hours
                    ->orWhere('addresses.seen', '<', Carbon::now()->subDay());  // Checks if 'seen' is older than 24 hours
            })
            // ->where('seen', 0)
            ->whereNull('follow_up_date')
            // ->where(function ($query) {
            //     $query->where('addresses.updated_at', '<', $now->subDay())
            //         ->orWhere(function ($subQuery) {
            //             $subQuery->where('addresses.updated_at', '>=', $now->subDay())
            //                 ->whereNull('addresses.feedback');
            //         });
            // })
            ->where(function ($query) {
                // Combine conditions for addresses with or without notreached entries
                $query->whereHas('notreached', function ($q) {
                    // Remove the condition on notreached created_at since we are using updated_at on Address
                })
                    ->withCount(['notreached'])
                    ->having('notreached_count', '<=', 10)
                    ->orWhereDoesntHave('notreached');
            })
            ->select('addresses.*')
            ->paginate($addressesPerPage);

        // dd($addresses->first());
        // Use pagination to control the number of results per page
        // Get the first address from the paginated results
        $address = $addresses->first();

        // Check if there are no more addresses to process
        if ($addresses->isEmpty()) {

            Log::channel('address')->warning('No Addresses to Process', [
                'timestamp' => $now->toDateTimeString(),
                'user_id' => auth()->id(),
            ]);

            return response()->json(['message' => 'No more addresses to process'], 404);
        }

        $address->seen = $now;
        $address->save();
        // dd($address);

        Log::channel('address')->info('Address Processed', [
            'timestamp' => $now->toDateTimeString(),
            'user_id' => auth()->id(),
            'address' => $address->toArray(),
        ]);

        return $address;
    }
}
