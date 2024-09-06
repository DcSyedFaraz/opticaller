<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Address;
use App\Models\GlobalLockedFields;
use App\Models\NotReached;
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

    public function break_end(Request $request, $id): void
    {
        // dd($request->all(), $id);
        $timeLog = new Activity();
        $timeLog->user_id = auth()->id();
        $timeLog->address_id = $id;
        $timeLog->activity_type = 'break';
        // $timeLog->ending_time = Carbon::now()->format('Y-m-d H:i:s');
        // $totalTime = $timeLog->ending_time->diffInSeconds($timeLog->starting_time);
        $timeLog->total_duration = $request->break_duration;
        $timeLog->save();

        // return response()->json($timeLog);
    }

    public function seen($id): void
    {
        $address = Address::find($id);
        $address->seen = 0;
        $address->save();
    }
    public function stopTracking(Request $request)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validate([
                'personal_notes' => 'nullable|string',
                'interest_notes' => 'nullable|string',
                'address' => 'required|array',
                'total_duration' => 'required',
                'address.id' => 'required|integer|exists:addresses,id',
                'address.company_name' => 'required|string',
                'address.salutation' => 'required|string',
                'address.first_name' => 'required|string',
                'address.last_name' => 'required|string',
                'address.street_address' => 'required|string',
                'address.postal_code' => 'required|string',
                'address.city' => 'required|string',
                'address.website' => 'nullable|url',
                'address.contact_id' => 'nullable|string',
                'address.phone_number' => 'required|string',
                'address.email_address_system' => 'required|email',
                'address.email_address_new' => 'nullable|email',
                'address.feedback' => 'required|string',
                'address.follow_up_date' => 'nullable|date|after:today',
            ], [
                'personal_notes.string' => 'Personal notes must be a string',
                'interest_notes.string' => 'Interest notes must be a string',
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
                'address.contact_id.string' => 'Contact ID must be a string',
                'address.website.url' => 'Website must be a valid URL',
                'address.phone_number.required' => 'Phone number is required',
                'address.phone_number.string' => 'Phone number must be a string',
                'address.email_address_system.required' => 'Email address is required',
                'address.feedback.required' => 'Feedback is required',
                'address.email_address_system.email' => 'Email address must be a valid email address',
                'address.email_address_new.email' => 'New email address must be a valid email address',
                'address.feedback.string' => 'Feedback must be a string',
                'address.follow_up_date.after' => 'Follow up date must be after today',
                'address.user_id.required' => 'User ID is required',
            ]);

            $addressID = $validatedData['address']['id'];
            // dd($validatedData['address']);
            $address = Address::find($addressID);

            if (!$address) {
                DB::rollBack();
                return response()->json(['error' => 'Address not found'], 404);
            }

            if ($validatedData['address']['feedback'] == 'Delete Address') {
                $address->delete();
                // DB::commit();
                // return response()->json(['message' => 'Address deleted successfully']);
            } else {

                $address->update($validatedData['address']);

                if ($request->notreached == true) {
                    NotReached::create(['address_id' => $address->id]);
                    $address->follow_up_date = null;
                    $address->feedback = null;
                }

                if ($address->follow_up_date) {
                    $address->follow_up_date = Carbon::parse($address->follow_up_date)->setTimezone('Europe/Berlin');
                }

                $address->seen = 0;
                $address->save();

                $seconds = $validatedData['total_duration'];
                $timeLog = new Activity();
                $timeLog->activity_type = 'call';
                $timeLog->user_id = auth()->id();
                $timeLog->address_id = $addressID;
                $timeLog->total_duration = $seconds;
                $timeLog->save();

                if (!empty($validatedData['personal_notes']) || !empty($validatedData['interest_notes'])) {
                    $timeLog->notes()->create([
                        'personal_notes' => $validatedData['personal_notes'] ?? null,
                        'interest_notes' => $validatedData['interest_notes'] ?? null,
                    ]);
                }

            }
            DB::commit();


            $addressService = new AddressService();
            $address = $addressService->getDueAddress();
            $globalLockedFields = GlobalLockedFields::firstOrCreate()->locked_fields;

            return response()->json(['address' => $address, 'lockfields' => $globalLockedFields]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred', 'details' => $e->getMessage()], 500);
        }
    }

}
