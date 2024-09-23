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

        $dueAddress = Address::with('calLogs.notes', 'subproject.projects', 'subproject.feedbacks', 'calLogs.users')
            ->whereIn('sub_project_id', $subProjectIds)
            ->where(function ($query) use ($now) {
                $query->where('follow_up_date', '<=', $now)
                    ->orWhere('follow_up_date', '=', $now);
            })
            ->orderBy('follow_up_date', 'asc')
            ->first();

        if ($dueAddress) {
            $dueAddress->seen = Carbon::now();
            $dueAddress->save();
            return $dueAddress;
        }


        $addressesPerPage = 1;

        // Fetch addresses dynamically
        $addresses = Address::with('calLogs.notes', 'subproject.projects', 'subproject.feedbacks', 'calLogs.users', 'project')
            // ->join('sub_projects', 'addresses.sub_project_id', '=', 'sub_projects.id')
            // ->orderBy('sub_projects.priority', 'desc')
            ->when($dueAddress, function ($query) {
                $query->join('sub_projects', 'addresses.sub_project_id', '=', 'sub_projects.id')
                    ->orderBy('sub_projects.priority', 'desc');
            })
            ->whereIn('sub_project_id', $subProjectIds)
            // ->where('seen', 0)
            ->where(function ($query) {
                $query->whereNull('addresses.seen')  // Checks if 'seen' is null (empty)
                    ->orWhere('addresses.seen', '<', Carbon::now()->subDay());  // Checks if 'seen' is older than 24 hours
            }) // Apply condition on Address's updated_at
            ->where(function ($query) {
                $query->where('addresses.updated_at', '<', Carbon::now()->subDay())
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('addresses.updated_at', '>=', Carbon::now()->subDay())
                            ->whereNull('addresses.feedback');
                    });
            })
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
            return response()->json(['message' => 'No more addresses to process'], 404);
        }

        // Process the retrieved address
        // For example, mark it as seen
        $address->seen = Carbon::now();
        $address->save();
        // dd($address->project);

        return $address;
    }
}
