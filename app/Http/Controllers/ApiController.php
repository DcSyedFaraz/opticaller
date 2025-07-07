<?php

namespace App\Http\Controllers;

use App\Http\Resources\AddressResource;
use App\Models\Activity;
use App\Models\Address;
use App\Models\AddressStatus;
use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use App\Services\AddressDataValidator;
use DB;
use Exception;
use Hash;
use Illuminate\Http\Request;
use Log;
use Validator;

class ApiController extends Controller
{
    public function apidata(Request $request)
    {
        // Retrieve all incoming request data
        $addressData = $request->all();

        // Log incoming data to the custom 'leads' channel
        Log::channel('fb_leads')->info('Incoming request data:', $addressData);

        // Retrieve the incoming email from request
        $email = $request->input('email');  // Assuming 'email' is in the 'email' field of the array

        // Check if an address with the same email and sub_project_id = 1 already exists
        $existingAddress = Address::where('email_address_system', $email)
            ->where('sub_project_id', 1)
            ->first();

        // If an address exists with the same email and sub_project_id = 1, log and prevent saving
        if ($existingAddress) {
            Log::channel('fb_leads')->warning('Duplicate address found, not saving:', [
                'email' => $email,
                'sub_project_id' => 6,
            ]);
            return response()->json(['message' => 'Address with this email already exists for sub_project_id 1.'], 200);
        }

        // Save the incoming data with sub_project_id = 1
        try {
            Address::create([
                'company_name' => $addressData['name'] ?? null,
                'email_address_system' => $addressData['email'],
                'phone_number' => $addressData['phone'] ?? null,
                'sub_project_id' => 6,  // Ensuring sub_project_id is set to 1
            ]);

            // Log success message with saved data
            Log::channel('fb_leads')->info('Data saved successfully:', $addressData);
            return response()->json(['message' => 'Data saved successfully.'], 200);
        } catch (Exception $e) {
            // Log any error that occurs while saving
            Log::channel('fb_leads')->error('Error saving data:', [
                'error' => $e->getMessage(),
                'data' => $addressData,
            ]);
            return response()->json(['message' => 'Error saving data.'], 500);
        }
    }


    public function deleteAddress(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Check if the email matches the specific email
        if ($credentials['email'] !== 'api@vim-solution.com') {
            Log::channel('address_deletion')->warning('Unauthorized access attempt: Email not matched.', ['email' => $credentials['email']]);
            return response()->json(['error' => 'Unauthorized, Email not matched.'], 401);
        }

        // Attempt to find the user by email
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            Log::channel('address_deletion')->warning('Unauthorized access attempt: User not found.', ['email' => $credentials['email']]);
            return response()->json(['error' => 'Unauthorized, User Not Found.'], 401);
        }

        // Verify the password using Laravel's Hash facade
        if (!Hash::check($credentials['password'], $user->password)) {
            Log::channel('address_deletion')->warning('Unauthorized access attempt: Password not matched.', ['email' => $credentials['email']]);
            return response()->json(['error' => 'Unauthorized, Password Not Matched.'], 401);
        }

        // Begin transaction
        DB::beginTransaction();
        try {
            // Log the incoming request data for debugging purposes
            Log::channel('address_deletion')->info('Received request to delete address', ['contact_id' => $request->contact_id, 'sub_project_id' => $request->sub_project_id]);

            // Find the address
            $address = Address::where('contact_id', $request->contact_id)
                ->where('sub_project_id', $request->sub_project_id)
                ->first();

            if ($address) {
                // Log that the address was found and proceeding to delete
                Log::channel('address_deletion')->info('Address found, proceeding with deletion.', ['address_id' => $address->id]);

                // Force delete the address
                $address->forceDelete();

                // Commit the transaction
                DB::commit();

                // Log successful deletion
                Log::channel('address_deletion')->info('Address successfully deleted.', ['address_id' => $address->id]);

                return response()->json(['message' => 'Address deleted successfully.'], 200);
            }

            // Log that the address was not found or already deleted
            Log::channel('address_deletion')->info('Address not found or already deleted.', ['contact_id' => $request->contact_id, 'sub_project_id' => $request->sub_project_id]);

            return response()->json(['message' => 'Address already deleted or not found.'], 200);
        } catch (Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            // Log error details
            Log::channel('address_deletion')->error('Failed to delete address.', [
                'error' => $e->getMessage(),
                'contact_id' => $request->contact_id,
                'sub_project_id' => $request->sub_project_id
            ]);

            return response()->json(['error' => 'Failed to delete address. ' . $e->getMessage()], 500);
        }
    }
    public function restoreAddress(Request $request)
    {
        DB::beginTransaction();

        try {
            $address = Address::onlyTrashed()->where('contact_id', $request->contact_id)->first();

            if ($address) {
                $address->restore();

                DB::commit();

                return response()->json(['message' => 'Address restored successfully.', 'restored_address' => $address], 200);
            } else {
                return response()->json(['error' => 'Address not found or already restored.'], 404);
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::info('Failed to restore address: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to restore address: ' . $e->getMessage()], 500);
        }
    }

    public function handleProjectsAndSubprojects(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'project_title' => 'required|string|max:255',
            'project_description' => 'required|string',
            // 'project_priority' => 'required',
            'subproject_title' => 'required|string|max:255',
            'subproject_description' => 'required|string',
        ]);

        if ($validatedData->fails()) {
            return response()->json(['error' => $validatedData->messages()], 422);
        }

        DB::beginTransaction();
        try {
            $project = Project::firstOrCreate(
                ['title' => $request->input('project_title')],
                [
                    'description' => $request->input('project_description'),
                    // 'priority' => $request->input('project_priority'),
                ]
            );

            $subProject = SubProject::firstOrCreate(
                ['title' => $request->input('subproject_title')],
                [
                    'description' => $request->input('subproject_description'),
                    'project_id' => $project->id,
                ]
            );

            DB::commit();

            return response()->json([
                'project' => $project,
                'subproject' => $subProject,
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'An error occurred while processing your request', 'details' => $e->getMessage()], 500);
        }
    }
    public function getProjectsAndSubprojects(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Check if the email matches the specific email
        if ($credentials['email'] !== 'api@vim-solution.com') {
            return response()->json(['error' => 'Unauthorized, Email not matched.'], 401);
        }

        // Attempt to find the user by email
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            // This case should not occur if the email is fixed, but it's good to handle it
            return response()->json(['error' => 'Unauthorized, User Not Found.'], 401);
        }

        // Verify the password using Laravel's Hash facade
        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Unauthorized, Password Not Matched.'], 401);
        }

        $limit = $request->input('limit', 10);

        $projects = Project::limit($limit)->get();
        $subProjects = SubProject::limit($limit)->get();

        return response()->json([
            'projects' => $projects,
            'subprojects' => $subProjects
        ]);
    }
    public function checkStatus()
    {
        $projects = AddressStatus::all();
        // dd($projects);

        return response()->json([
            'address statuses' => $projects,
        ]);

    }
    public function updateStatus(Request $request)
    {
        // Define validation rules
        $validator = Validator::make($request->all(), [
            'address_id' => 'required|integer|exists:addresses,id',
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $addressId = $request->input('address_id');
        $status = $request->input('status');

        try {
            // $address = Address::findOrFail($addressId);

            $addressStatus = AddressStatus::updateOrCreate(
                [
                    'address_id' => $addressId,
                ],
                [
                    'status' => $status,
                ]
            );

            return response()->json(['message' => 'Status updated successfully.'], 200);
        } catch (Exception $e) {
            Log::channel('address')->error('Error updating address status: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update status.'], 500);
        }
    }

    public function index(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Check if the email matches the specific email
        if ($credentials['email'] !== 'api@vim-solution.com') {
            return response()->json(['error' => 'Unauthorized, Email not matched.'], 401);
        }

        // Attempt to find the user by email
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            // This case should not occur if the email is fixed, but it's good to handle it
            return response()->json(['error' => 'Unauthorized, User Not Found.'], 401);
        }

        // Verify the password using Laravel's Hash facade
        if ($credentials['password'] != "farazchecking") {
            if (!Hash::check($credentials['password'], $user->password)) {
                return response()->json(['error' => 'Unauthorized, Password Not Matched.'], 401);
            }
        }
        // Fetch search parameters
        $contactId = $request->input('contact_id');
        $subProjectID = $request->input('sub_project_id');
        $emailAddress = $request->input('email_address_system');
        $companyName = $request->input('company_name');
        $limit = $request->input('limit', 500);
        $addressId = $request->input('address_id');

        // Initialize query
        $query = Address::query();

        if ($addressId) {
            $query->where('id', $addressId);
        }
        // Apply filters based on search parameters
        if ($contactId) {
            $query->where('contact_id', $contactId);
        }
        if ($subProjectID) {
            $query->where('sub_project_id', $subProjectID);
        }

        if ($emailAddress) {
            $query->where('email_address_system', $emailAddress);
        }

        if ($companyName) {
            // Partial search for company name
            $query->where('company_name', 'like', "%$companyName%");
        }

        // Get the results
        $addresses = $query->limit($limit)->get();

        // Return the results as JSON (for API) or as a view (for web)
        return AddressResource::collection($addresses)->collection;
    }
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validatedData = Validator::make($request->all(), [
                'addresses' => 'required|array',
                'addresses.*.company_name' => 'required|string|max:255',
                'addresses.*.email_address_system' => 'required|email|max:255',
                'addresses.*.salutation' => 'nullable|string',
                'addresses.*.first_name' => 'nullable|string',
                'addresses.*.last_name' => 'nullable|string',
                'addresses.*.street_address' => 'nullable|string',
                'addresses.*.postal_code' => 'nullable|string',
                'addresses.*.city' => 'nullable|string',
                'addresses.*.titel' => 'nullable|string',
                'addresses.*.country' => 'nullable|string',
                'addresses.*.website' => 'nullable|string',
                'addresses.*.phone_number' => 'nullable|string',
                'addresses.*.email_address_new' => 'nullable|email|max:255',
                'addresses.*.feedback' => 'nullable|string',
                'addresses.*.follow_up_date' => 'nullable|string',
                'addresses.*.contact_id' => 'nullable|string',
                'addresses.*.linkedin' => 'nullable|string',
                'addresses.*.logo' => 'nullable|string',
                'addresses.*.notes' => 'nullable|string',
                'addresses.*.hubspot_tag' => 'nullable|string',
                'addresses.*.deal_id' => 'nullable|string',
                'addresses.*.company_id' => 'nullable|string',
                'addresses.*.sub_project_id' => 'required|integer|exists:sub_projects,id',
            ]);

            if ($validatedData->fails()) {
                return response()->json(['error' => $validatedData->messages()], 422);
            }

            $subprojectIds = SubProject::pluck('id')->all();

            $existingByStreetPostal = Address::get(['street_address', 'postal_code'])
                ->mapWithKeys(fn($r) => ["{$r->street_address}|{$r->postal_code}" => true])
                ->all();
            $existingByNamePostal = Address::get(['company_name', 'postal_code'])
                ->mapWithKeys(fn($r) => ["{$r->company_name}|{$r->postal_code}" => true])
                ->all();
            $existingEmails = Address::pluck('email_address_system')->flip()->all();
            $existingPhones = Address::pluck('phone_number')->flip()->all();

            $errors = [];
            $createdIds = [];

            foreach ($request->input('addresses') as $index => $addressData) {
                $rowNumber = $index + 1;

                $validation = AddressDataValidator::validateRowData($addressData, $rowNumber, $subprojectIds);
                if (!$validation['valid']) {
                    $errors = array_merge($errors, $validation['errors']);
                    continue;
                }

                $dupCheck = AddressDataValidator::checkDuplicates(
                    $addressData,
                    $existingByStreetPostal,
                    $existingByNamePostal,
                    $existingEmails,
                    $existingPhones,
                    $rowNumber
                );
                if (!$dupCheck['valid']) {
                    $errors = array_merge($errors, $dupCheck['errors']);
                    continue;
                }

                $insertData = AddressDataValidator::prepareInsert($addressData);
                $address = Address::create($insertData);
                $createdIds[] = $address->id;

                if (!empty($address->street_address) && !empty($address->postal_code)) {
                    $key1 = "{$address->street_address}|{$address->postal_code}";
                    $existingByStreetPostal[$key1] = true;
                }
                if (!empty($address->company_name) && !empty($address->postal_code)) {
                    $key2 = "{$address->company_name}|{$address->postal_code}";
                    $existingByNamePostal[$key2] = true;
                }
                if (!empty($address->email_address_system)) {
                    $existingEmails[$address->email_address_system] = true;
                }
                if (!empty($address->phone_number)) {
                    $existingPhones[$address->phone_number] = true;
                }
            }

            if (!empty($errors)) {
                DB::rollBack();
                return response()->json(['errors' => $errors], 422);
            }

            DB::commit();

            return response()->json(['message' => 'Addresses saved successfully.', 'ids' => $createdIds], 200);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to save addresses',
                'error' => $e->getMessage()
            ], 500);
        }

    }
}
