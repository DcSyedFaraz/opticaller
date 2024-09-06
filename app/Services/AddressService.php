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


        $addressesPerPage = 1;

        // Fetch addresses dynamically
        $addresses = Address::with('calLogs.notes', 'subproject.projects', 'calLogs.users', 'project')
            ->whereIn('sub_project_id', $subProjectIds)
            // ->where('seen', 0)
            ->where('addresses.updated_at', '<', Carbon::now()->subDay())  // Apply condition on Address's updated_at
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
            ->leftJoin('sub_projects', 'addresses.sub_project_id', '=', 'sub_projects.id')
            ->leftJoin('projects', 'sub_projects.project_id', '=', 'projects.id')
            ->orderBy('projects.priority', 'desc')
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
        $address->seen += 1;
        $address->save();
        // dd($address->project);

        return $address;
    }
}
