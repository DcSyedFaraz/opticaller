<?php

namespace App\Http\Controllers;

use App;
use App\Models\Activity;
use App\Models\Address;
use App\Models\Feedback;
use App\Models\GlobalLockedFields;
use App\Models\NotReached;
use App\Services\AddressService;
use Carbon\Carbon;
use Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;

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

    public function stopTracking(Request $request)
    {
        // dd();
        DB::beginTransaction();

        try {
            // Define validation rules
            $rules = [
                'personal_notes' => 'nullable|string',
                'interest_notes' => 'nullable|string',
                'address' => 'required|array',
                'total_duration' => 'required|integer',
                'address.id' => 'required|integer|exists:addresses,id',
                'address.company_name' => 'required|string',
                'address.salutation' => 'nullable|string',
                'address.street_address' => 'nullable|string',
                'address.postal_code' => 'nullable|string',
                'address.city' => 'nullable|string',
                'address.country' => 'nullable|string',
                'address.website' => 'nullable|string',
                'address.titel' => 'nullable|string',
                'address.phone_number' => 'nullable|string',
                'address.email_address_system' => [
                    'required',
                    'email',
                    'regex:/^((?!no-mail).)*$/i'
                ],
                'address.email_address_new' => 'nullable|email',
                'address.feedback' => 'required|string',
                'address.follow_up_date' => 'nullable|date|after:today',
                'notreached' => 'sometimes|boolean', // Ensure 'notreached' is boolean
            ];

            // Conditional validation for first_name and last_name based on salutation
            if ($request->address['salutation'] !== 'Sehr geehrte Damen und Herren') {
                $rules['address.first_name'] = 'nullable|string|min:3';
                $rules['address.last_name'] = 'nullable|string|min:3';
            } else {
                $rules['address.first_name'] = 'nullable|string';
                $rules['address.last_name'] = 'nullable|string';
            }

            // Fetch feedback
            $feedback = Feedback::where('value', $request->address['feedback'])->first();

            // Apply validation only if saveEdits is true and feedback requires validation
            if ($request->saveEdits && $feedback && !$feedback->no_validation) {

                $validatedData = $request->validate($rules, [
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
                    'address.first_name.min' => 'The first name must be at least 3 characters long.',
                    'address.last_name.required' => 'Last name is required',
                    'address.last_name.min' => 'The last name must be at least 3 characters long.',
                    'address.last_name.string' => 'Last name must be a string',
                    'address.street_address.required' => 'Street address is required',
                    'address.street_address.string' => 'Street address must be a string',
                    'address.postal_code.required' => 'Postal code is required',
                    'address.postal_code.string' => 'Postal code must be a string',
                    'address.city.required' => 'City is required',
                    'address.country.required' => 'country is required',
                    'address.city.string' => 'City must be a string',
                    'address.contact_id.string' => 'Contact ID must be a string',
                    'address.phone_number.required' => 'Phone number is required',
                    'address.phone_number.string' => 'Phone number must be a string',
                    'address.email_address_system.required' => 'Email address is required',
                    'address.feedback.required' => 'Feedback is required',
                    'address.email_address_system.email' => 'Email address must be a valid email address',
                    'address.email_address_new.email' => 'New email address must be a valid email address',
                    'address.email_address_system.regex' => 'The email address must not contain "no-mail".',
                    'address.feedback.string' => 'Feedback must be a string',
                    'address.follow_up_date.after' => 'Follow up date must be after today',
                ]);
            } else {
                // If not validating, ensure 'address' and 'feedback' exist to prevent issues
                $validatedData = $request->only(['address', 'personal_notes', 'interest_notes', 'total_duration']);
                $validatedData['address']['feedback'] = $validatedData['address']['feedback'] ?? '';
            }

            // Ensure 'feedback' is present and not empty when saveEdits is true
            if ($request->saveEdits && empty($validatedData['address']['feedback'])) {
                DB::rollBack();
                return response()->json(['error' => 'Feedback is required when saving edits.'], 422);
            }

            $addressID = $validatedData['address']['id'];
            $address = Address::find($addressID);

            if (!$address) {
                DB::rollBack();
                return response()->json(['error' => 'Address not found'], 404);
            }

            // Determine if 'notreached' is true
            $notreached = filter_var($request->notreached, FILTER_VALIDATE_BOOLEAN);

            // testing hook
            $webhookUrl = 'https://hook.eu1.make.com/9tjpua1qx1dhgil7zbisfhaucr11hqge';

            // live hook
            // $webhookUrl = 'https://hook.eu1.make.com/5qruvb50swmc3wdj7obdzbxgosov09jf';

            // Enhanced condition to trigger webhook
            if (!$notreached && !empty($validatedData['address']['feedback'])) {
                // Check if the application is not running in the 'local' environment
                Log::info('Triggering webhook for contact ID: ' . $address->contact_id);

                // Option 1: Directly trigger (synchronous)
                // Uncomment if you prefer synchronous execution

                $response = Http::post($webhookUrl, [
                    'ID' => $address->contact_id,
                    'Sub_Project' => $address->sub_project_id
                ]);

                if ($response->successful()) {
                    Log::info('Webhook successfully triggered for contact ID: ' . $address->contact_id);
                } else {
                    Log::error('Webhook failed for contact ID: ' . $address->contact_id . '. Response: ' . $response->body());
                }
                // if (App::environment('local')) {
                //     // Optional: Log webhook trigger


                //     // Option 2: Dispatch a job for asynchronous processing (recommended)
                //     // TriggerWebhook::dispatch($validatedData['address']['contact_id'], $webhookUrl);
                // } else {
                //     // Optional: Log why webhook is not triggered
                //     Log::info('Webhook not triggered. Application is running in the local environment.');
                // }
            } else {
                // Optional: Log why webhook is not triggered
                Log::info('Webhook not triggered for contact ID: ' . $address->contact_id . '. Conditions - saveEdits: ' . ($request->saveEdits ? 'true' : 'false') .
                    ', notreached: ' . ($notreached ? 'true' : 'false') .
                    ', feedback: ' . (!empty($validatedData['address']['feedback']) ? 'present' : 'empty'));
            }


            if ($validatedData['address']['feedback'] == 'Delete Address') {
                $address->delete();
            } else {
                // Clean up unwanted fields
                $fieldsToUnset = ['cal_logs', 'subproject', 'feedbacks', 'project'];
                foreach ($fieldsToUnset as $field) {
                    if (isset($validatedData['address'][$field])) {
                        unset($validatedData['address'][$field]);
                    }
                }

                // Update address
                $address->update($validatedData['address']);

                if ($notreached) {
                    NotReached::create(['address_id' => $address->id]);
                    $address->follow_up_date = null;
                    $address->feedback = 'notreached';
                }

                if ($address->follow_up_date) {
                    $address->follow_up_date = Carbon::parse($address->follow_up_date)->setTimezone('Europe/Berlin');
                    // dd($request->address['follow_up_date'], $address->follow_up_date);
                }

                $address->save();

                // Log activity
                $seconds = $validatedData['total_duration'];
                $timeLog = new Activity();
                $timeLog->activity_type = 'call';
                $timeLog->user_id = auth()->id();
                $timeLog->address_id = $addressID;
                $timeLog->total_duration = $seconds;
                $timeLog->feedback = $validatedData['address']['feedback'];
                $timeLog->contact_id = $address->contact_id;
                $timeLog->sub_project_id = $request->address['subproject']['title'];
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
            // Log the exception for debugging
            Log::error('Error in stopTracking: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred', 'details' => $e->getMessage()], 500);
        }
    }


}
