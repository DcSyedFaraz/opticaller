<?php

namespace App\Http\Controllers;

use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Models\Project;
use App\Models\SubProject;
use DB;
use Exception;
use Illuminate\Http\Request;
use Validator;

class ApiController extends Controller
{
    // public function subprojects(Request $request)
    // {
    //     $validatedData = Validator::make($request->all(), [
    //         'title' => 'required|string|max:255',
    //         'description' => 'required|string',
    //         'project_id' => 'required|exists:projects,id',
    //     ]);
    //     if ($validatedData->fails()) {
    //         return response()->json(['error' => $validatedData->messages()], 422);
    //     }
    //     $subProject = SubProject::create([
    //         'project_id' => $request->input('project_id'),
    //         'title' => $request->input('title'),
    //         'description' => $request->input('description'),
    //     ]);

    //     return response()->json($subProject);
    // }
    // public function projects(Request $request)
    // {
    //     $validatedData = Validator::make($request->all(), [
    //         'title' => 'required|string|max:255',
    //         'description' => 'required|string',
    //         'priority' => 'required',
    //         'color' => 'nullable',
    //     ]);
    //     if ($validatedData->fails()) {
    //         return response()->json(['error' => $validatedData->messages()], 422);
    //     }
    //     $project = Project::create([
    //         'title' => $request->input('title'),
    //         'description' => $request->input('description'),
    //         'priority' => $request->input('priority'),
    //     ]);


    //     return response()->json($project);
    // }
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
                'addresses.*.country' => 'nullable|string',
                'addresses.*.website' => 'nullable|string',
                'addresses.*.phone_number' => 'nullable|string',
                'addresses.*.email_address_new' => 'nullable|email|max:255',
                'addresses.*.feedback' => 'nullable|string',
                'addresses.*.follow_up_date' => 'nullable|string',
                'addresses.*.contact_id' => 'nullable|string',
                'addresses.*.linkedin' => 'nullable|string',
                'addresses.*.logo' => 'nullable|string',
                'addresses.*.sub_project_id' => 'required|integer|exists:sub_projects,id',
                // 'addresses.*.priority' => 'nullable|integer',
                'addresses.*.seen' => 'nullable|integer',
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
                    'priority' => $addressData['priority'] ?? 0,
                    'seen' => $addressData['seen'] ?? 0,
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
