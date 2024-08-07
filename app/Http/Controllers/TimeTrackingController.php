<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Address;
use App\Services\AddressService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimeTrackingController extends Controller
{
    public function startTracking(Request $request)
    {
        $timeLog = Activity::create([
            'user_id' => auth()->id(),
            'address_id' => $request->address_id,
            'starting_time' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        return response()->json($timeLog);
    }

    public function pauseTracking(Request $request, $id)
    {
        $timeLog = Activity::create([
            'user_id' => auth()->id(),
            'address_id' => $id,
            'activity_type' => 'break',
            'starting_time' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        return response()->json($timeLog);
    }

    public function resumeTracking(Request $request, $id)
    {
        $timeLog = Activity::find($id);
        $timeLog->ending_time = Carbon::now()->format('Y-m-d H:i:s');
        $totalTime = $timeLog->ending_time->diffInSeconds($timeLog->starting_time);
        $timeLog->total_duration = $totalTime;
        $timeLog->save();

        return response()->json($timeLog);
    }

    public function stopTracking(Request $request, $id)
    {
        DB::beginTransaction();

        $validatedData = $request->validate([
            'call_attempts' => 'required|integer',
            'personal_notes' => 'nullable|string',
            'address' => 'required|array',
            'address.id' => 'required|integer|exists:addresses,id',
            'address.company_name' => 'required|string',
            'address.salutation' => 'required|string',
            'address.first_name' => 'required|string',
            'address.last_name' => 'required|string',
            'address.street_address' => 'required|string',
            'address.postal_code' => 'required|string',
            'address.city' => 'required|string',
            'address.website' => 'nullable|url',
            'address.phone_number' => 'required|string',
            'address.email_address_system' => 'required|email',
            'address.email_address_new' => 'nullable|email',
            'address.interest_notes' => 'nullable|string',
            'address.feedback' => 'nullable|string',
            'address.follow_up_date' => 'nullable|date|after:today',
            'address.user_id' => 'required|integer|exists:users,id',
            'address.project_id' => 'nullable|integer|exists:projects,id',
        ], [
            'call_attempts.required' => 'Call attempts is required',
            'call_attempts.integer' => 'Call attempts must be an integer',
            'personal_notes.string' => 'Personal notes must be a string',
            'address.id.required' => 'Address ID is required',
            'address.id.integer' => 'Address ID must be an integer',
            'address.id.exists' => 'Address ID does not exist',
            'address.company_name.required' => 'Company name is required',
            'address.company_name.string' => 'Company name must be a string',
            'address.salutation.required' => 'Salutation is required',
            'address.salutation.string' => 'Salutation must be a string',
            'address.first_name.required' => 'First name is required',
            'address.first_name.string' => 'First name must be a string',
            'address.last_name.required' => 'Last name is required',
            'address.last_name.string' => 'Last name must be a string',
            'address.street_address.required' => 'Street address is required',
            'address.street_address.string' => 'Street address must be a string',
            'address.postal_code.required' => 'Postal code is required',
            'address.postal_code.string' => 'Postal code must be a string',
            'address.city.required' => 'City is required',
            'address.city.string' => 'City must be a string',
            'address.website.url' => 'Website must be a valid URL',
            'address.phone_number.required' => 'Phone number is required',
            'address.phone_number.string' => 'Phone number must be a string',
            'address.email_address_system.required' => 'Email address is required',
            'address.email_address_system.email' => 'Email address must be a valid email address',
            'address.email_address_new.email' => 'New email address must be a valid email address',
            'address.interest_notes.string' => 'Interest notes must be a string',
            'address.feedback.string' => 'Feedback must be a string',
            'address.follow_up_date.after' => 'Follow up date must be after today',
            'address.user_id.required' => 'User ID is required',
            'address.user_id.integer' => 'User ID must be an integer',
            'address.user_id.exists' => 'User ID does not exist',
            'address.project_id.integer' => 'Project ID must be an integer',
            'address.project_id.exists' => 'Project ID does not exist',
        ]);

        // dd($request->all());
        // Save the address data
        $address = Address::find($validatedData['address']['id']);
        $address->update($validatedData['address']);

        if ($address->follow_up_date) {
            $address->follow_up_date = Carbon::parse($address->follow_up_date)->setTimezone('Europe/Berlin');

            $address->seen = 0;
            $address->save();
        }

        unset($validatedData['address']);

        $timeLog = Activity::find($id);
        $timeLog->ending_time = Carbon::now()->format('Y-m-d H:i:s');
        $effectiveTime = $timeLog->calculateEffectiveTime();
        $timeLog->total_duration = $effectiveTime;
        $timeLog->save();

        $timeLog->notes()->Create($validatedData);

        DB::commit();

        $addressService = new AddressService();
        $address = $addressService->getDueAddress();

        return response()->json(['address' => $address]);
    }
}
