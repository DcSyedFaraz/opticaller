<?php
namespace App\Services;

use App\Models\Address;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class AddressService
{
    public function getDueAddress()
    {
        $now = Carbon::now();
        $subProjectIds = auth()->user()->subProjects()->pluck('sub_project_id');
        // Session::forget('addresses');

        $dueAddress = Address::with('calLogs.notes', 'subproject.projects', 'calLogs.users')
            ->whereIn('sub_project_id', $subProjectIds)
            ->where(function ($query) use ($now) {
                $query->where('follow_up_date', '<=', $now)
                    ->orWhere('follow_up_date', '=', $now);
            })
            ->orderBy('follow_up_date', 'asc')
            ->first();

        if ($dueAddress) {
            $dueAddress->update(['seen' => 1]);
            return $dueAddress;
        }

        // If no due address is found, handle session-based addresses
        // if (!Session::has('addresses')) {
        //     $addresses = Address::with('calLogs.notes', 'subproject.projects')
        //     ->whereIn('sub_project_id', $subProjectIds)
        //     ->where('seen', 0)
        //     ->where(function ($query) {
        //         // Condition for addresses with notreached entries
        //         $query->whereHas('notreached', function ($q) {
        //             $q->where('created_at', '<', Carbon::now()->subDay());
        //         })
        //         ->withCount(['notreached'])
        //         ->having('notreached_count', '<=', 10)
        //         // OR condition for addresses without notreached entries
        //         ->orWhereDoesntHave('notreached');
        //     })
        //     ->orderBy('priority', 'desc')
        //     ->get();

        //     // dd($addresses);
        //     Session::put('addresses', $addresses);
        // }

        // $storedAddresses = Session::get('addresses');
        // $address = $storedAddresses->shift();

        // Session::put('addresses', $storedAddresses);

        // if ($storedAddresses->isEmpty()) {
        //     Session::forget('addresses');
        // }
        // if ($address) {

        //     $address->update(['seen' => 1]);
        // }
        $addressesPerPage = 1;

        // Fetch addresses dynamically
        $addresses = Address::with('calLogs.notes', 'subproject.projects', 'calLogs.users')
            ->whereIn('sub_project_id', $subProjectIds)
            ->where('seen', 0)
            ->where('updated_at', '<', Carbon::now()->subDay())  // Apply condition on Address's updated_at
            ->where(function ($query) {
                // Combine conditions for addresses with or without notreached entries
                $query->whereHas('notreached', function ($q) {
                    // Remove the condition on notreached created_at since we are using updated_at on Address
                })
                    ->withCount(['notreached'])
                    ->having('notreached_count', '<=', 10)
                    ->orWhereDoesntHave('notreached');
            })
            ->orderBy('priority', 'desc')
            ->paginate($addressesPerPage);  // Use pagination to control the number of results per page

        // Get the first address from the paginated results
        $address = $addresses->first();

        // Check if there are no more addresses to process
        if ($addresses->isEmpty()) {
            return response()->json(['message' => 'No more addresses to process'], 404);
        }

        // Process the retrieved address
        // For example, mark it as seen
        $address->seen = 1;
        $address->save();


        return $address;
    }
}
