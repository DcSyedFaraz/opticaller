<?php

namespace App\Http\Controllers;

use App\Http\Resources\AddressResource;
use App\Models\Activity;
use App\Models\Address;
use App\Models\Project;
use App\Models\SubProject;
use DB;
use Exception;
use Illuminate\Http\Request;
use Log;
use Validator;

class ApiController extends Controller
{
    public function deleteAddress(Request $request)
    {
        if ($request->email !== 'max@vimtronix.com' || $request->password !== '#xf?$RsLko@grH5NME') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        if (!$request->contact_id || !$request->sub_project_id) {
            return response()->json(['error' => 'Both contact ID and sub-project ID are required.'], 422);
        }

        DB::beginTransaction();

        try {
            $address = Address::where('contact_id', $request->contact_id)->where('sub_project_id', $request->sub_project_id)->first();
            if ($address) {
                $address->forceDelete();
                DB::commit();

                return response()->json(['message' => 'Address deleted successfully.'], 200);
            }

            return response()->json(['message' => 'Address already deleted or not found.'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info('Failed to delete address.' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete address.' . $e->getMessage()], 500);
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
        if ($request->email !== 'max@vimtronix.com' || $request->password !== '#xf?$RsLko@grH5NME') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $validatedData = Validator::make($request->all(), [
            'limit' => 'nullable|integer|min:1',
        ]);

        if ($validatedData->fails()) {
            return response()->json(['error' => $validatedData->messages()], 422);
        }

        $limit = $request->input('limit', 10);

        $projects = Project::limit($limit)->get();
        $subProjects = SubProject::limit($limit)->get();

        return response()->json([
            'projects' => $projects,
            'subprojects' => $subProjects
        ]);
    }

    public function index(Request $request)
    {
        if ($request->email !== 'max@vimtronix.com' || $request->password !== '#xf?$RsLko@grH5NME') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        // Fetch search parameters
        $contactId = $request->input('contact_id');
        $emailAddress = $request->input('email_address_system');
        $companyName = $request->input('company_name');
        $limit = $request->input('limit', 500);

        // Initialize query
        $query = Address::query();

        // Apply filters based on search parameters
        if ($contactId) {
            $query->where('contact_id', $contactId);
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
            // Validate incoming request
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
                // 'addresses.*.priority' => 'nullable|integer',
            ]);
            if ($validatedData->fails()) {
                return response()->json(['error' => $validatedData->messages()], 422);
            }
            $data = $request->all();
            // Loop through each address and save it
            foreach ($data['addresses'] as $addressData) {
                Address::create([
                    'company_name' => $addressData['company_name'],
                    'salutation' => $addressData['salutation'] ?? null,
                    'first_name' => $addressData['first_name'] ?? null,
                    'last_name' => $addressData['last_name'] ?? null,
                    'street_address' => $addressData['street_address'] ?? null,
                    'postal_code' => $addressData['postal_code'] ?? null,
                    'city' => $addressData['city'] ?? null,
                    'country' => $addressData['country'] ?? null,
                    'website' => $addressData['website'] ?? null,
                    'phone_number' => $addressData['phone_number'] ?? null,
                    'email_address_system' => $addressData['email_address_system'],
                    'email_address_new' => $addressData['email_address_new'] ?? null,
                    'feedback' => $addressData['feedback'] ?? null,
                    'follow_up_date' => $addressData['follow_up_date'] ?? null,
                    'contact_id' => $addressData['contact_id'] ?? null,
                    'sub_project_id' => $addressData['sub_project_id'] ?? null,
                    'hubspot_tag' => $addressData['hubspot_tag'] ?? null,
                    'notes' => $addressData['notes'] ?? null,
                    'company_id' => $addressData['company_id'] ?? null,
                    'deal_id' => $addressData['deal_id'] ?? null,
                    'titel' => $addressData['titel'] ?? null,
                ]);
            }

            // Commit the transaction after successful execution
            DB::commit();

            // Return a success response
            return response()->json(['message' => 'Addresses saved successfully.'], 200);

        } catch (Exception $e) {
            // Roll back the transaction in case of any failure
            DB::rollBack();

            // Log the error (you can also use a logger here if needed)
            // Optionally return the error message
            return response()->json([
                'message' => 'Failed to save addresses',
                'error' => $e->getMessage()
            ], 500);
        }

    }
}
