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

        $dueAddress = Address::with('calLogs.notes','subproject.projects')
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
        if (!Session::has('addresses')) {
            $addresses = Address::with('calLogs.notes','subproject.projects')
            ->whereIn('sub_project_id', $subProjectIds)
                ->where(function ($query) {
                    $query->where('seen', 0)
                        ->orWhere('feedback', 'not_reachable');
                })
                ->orderBy('priority', 'desc')
                ->get();

                // dd($addresses);
            Session::put('addresses', $addresses);
        }

        $storedAddresses = Session::get('addresses');
        $address = $storedAddresses->shift();

        Session::put('addresses', $storedAddresses);

        if ($storedAddresses->isEmpty()) {
            Session::forget('addresses');
        }
        if($address){

            $address->update(['seen' => 1]);
        }

        return $address;
    }
}
