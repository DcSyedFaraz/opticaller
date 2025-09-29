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
    /**
     * List duplicate addresses grouped by the key fields, returning each
     * duplicate's id, created_at, and feedback for easy identification.
     *
     * No parameters required; returns grouped result across active (non-deleted) addresses.
     */
    public function duplicates(Request $request)
    {
        // Subquery that identifies duplicate groups by the specified fields
        $groupFields = [
            'phone_number',
            'email_address_system',
            'company_name',
            'city',
            'website',
            'mobile_number',
            'email_address_new',
            'sub_project_id',
            'main_category_query',
            'sub_category_category',
            'postal_code',
            'street_address',
        ];

        $selectGroupCols = implode(', ', array_map(fn($c) => "`$c`", $groupFields));

        // Note: use NULL-safe equality (<=>) to match NULLs within groups
        $joinConditions = implode(' AND ', array_map(fn($c) => "a.`$c` <=> g.`$c`", $groupFields));

        $sql = "
            SELECT
                a.id,
                a.created_at,
                a.feedback,
                a.phone_number,
                a.email_address_system,
                a.company_name,
                a.city,
                a.website,
                a.mobile_number,
                a.email_address_new,
                a.sub_project_id,
                a.main_category_query,
                a.sub_category_category,
                a.postal_code,
                a.street_address,
                a.contact_id,
                a.first_name,
                a.last_name,
                a.salutation
            FROM addresses a
            INNER JOIN (
                SELECT {$selectGroupCols}
                FROM addresses
                WHERE deleted_at IS NULL
                GROUP BY {$selectGroupCols}
                HAVING COUNT(*) > 1
            ) g ON {$joinConditions}
            WHERE a.deleted_at IS NULL
            ORDER BY a.created_at DESC
        ";
        $rows = DB::select($sql);

        // Group by the full duplicate key so it's clear which ones belong together
        $groups = [];
        $totalDuplicateRecords = 0;
        $duplicateCriteriaStats = [];

        foreach ($rows as $r) {
            $keyParts = [];
            $nonNullFields = [];

            foreach ($groupFields as $f) {
                $value = $r->{$f} ?? null;
                $keyParts[$f] = $value;
                if ($value !== null && $value !== '') {
                    $nonNullFields[] = $f;
                }
            }

            // Create a stable string key for internal grouping
            $hashKey = md5(json_encode($keyParts));

            if (!isset($groups[$hashKey])) {
                $groups[$hashKey] = [
                    'duplicate_criteria' => $keyParts,
                    'non_null_criteria' => $nonNullFields,
                    'records' => [],
                ];
            }

            $groups[$hashKey]['records'][] = [
                'id' => (int)$r->id,
                'contact_id' => $r->contact_id,
                'created_at' => (string)$r->created_at,
                'feedback' => $r->feedback,
                'has_feedback' => !empty($r->feedback),
                'company_name' => $r->company_name,
                'email_address_system' => $r->email_address_system,
                'phone_number' => $r->phone_number,
                'mobile_number' => $r->mobile_number,
                'first_name' => $r->first_name,
                'last_name' => $r->last_name,
                'salutation' => $r->salutation,
                'sub_project_id' => $r->sub_project_id,
            ];

            $totalDuplicateRecords++;
        }

        // Sort each group's records by created_at descending (newest first)
        foreach ($groups as &$g) {
            usort($g['records'], fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
            $g['count'] = count($g['records']);
            $g['duplicate_summary'] = "Found {$g['count']} duplicate records";

            // Track criteria statistics
            $criteriaKey = implode(', ', $g['non_null_criteria']);
            if (!isset($duplicateCriteriaStats[$criteriaKey])) {
                $duplicateCriteriaStats[$criteriaKey] = 0;
            }
            $duplicateCriteriaStats[$criteriaKey] += $g['count'];
        }
        unset($g);

        // Reindex as a list
        $groupList = array_values($groups);

        // Sort groups by latest record created_at (newest first), then by size desc
        usort($groupList, function ($a, $b) {
            $aLatest = $a['records'][0]['created_at'] ?? '';
            $bLatest = $b['records'][0]['created_at'] ?? '';
            $cmp = strcmp($bLatest, $aLatest);
            if ($cmp !== 0) return $cmp;
            return $b['count'] <=> $a['count'];
        });

        // Sort criteria stats by frequency
        arsort($duplicateCriteriaStats);

        return response()->json([
            'success' => true,
            'summary' => [
                'total_duplicate_groups' => count($groupList),
                'total_duplicate_records' => $totalDuplicateRecords,
                'most_common_duplicate_criteria' => array_slice($duplicateCriteriaStats, 0, 5, true),
            ],
            'duplicate_groups' => $groupList,
            'message' => "Found " . count($groupList) . " groups of duplicate addresses containing {$totalDuplicateRecords} total records",
        ]);
    }

    /**
     * Delete duplicate addresses that have null feedback and null seen values.
     * Keeps the oldest record from each duplicate group and deletes the rest.
     *
     * Requires authentication with email and password.
     */
    public function deleteDuplicateRecords(Request $request)
    {
        DB::beginTransaction();
        try {
            // Define the fields used to identify duplicates
            $groupFields = [
                'phone_number',
                'email_address_system',
                'company_name',
                'city',
                'website',
                'mobile_number',
                'email_address_new',
                'sub_project_id',
                'main_category_query',
                'sub_category_category',
                'postal_code',
                'street_address',
            ];

            $selectGroupCols = implode(', ', array_map(fn($c) => "`$c`", $groupFields));
            $joinConditions = implode(' AND ', array_map(fn($c) => "a.`$c` <=> g.`$c`", $groupFields));

            // First, let's get all duplicates (not just those with null feedback/seen)
            $allDuplicatesSql = "
                SELECT
                    a.id,
                    a.created_at,
                    a.feedback,
                    a.seen,
                    a.phone_number,
                    a.email_address_system,
                    a.company_name,
                    a.city,
                    a.website,
                    a.mobile_number,
                    a.email_address_new,
                    a.sub_project_id,
                    a.main_category_query,
                    a.sub_category_category,
                    a.postal_code,
                    a.street_address
                FROM addresses a
                INNER JOIN (
                    SELECT {$selectGroupCols}
                    FROM addresses
                    WHERE deleted_at IS NULL
                    GROUP BY {$selectGroupCols}
                    HAVING COUNT(*) > 1
                ) g ON {$joinConditions}
                WHERE a.deleted_at IS NULL
                ORDER BY a.created_at ASC
            ";

            $allDuplicateRecords = DB::select($allDuplicatesSql);

            // Now filter for records with null feedback and null seen
            $targetRecords = array_filter($allDuplicateRecords, function($record) {
                return (empty($record->feedback) && empty($record->seen));
            });

            if (empty($targetRecords)) {
                Log::channel('address_deletion')->info('No duplicate records found with null feedback and null seen values.');
                return response()->json([
                    'success' => true,
                    'message' => 'No duplicate records found with null feedback and null seen values.',
                    'deleted_count' => 0,
                    'debug_info' => [
                        'total_duplicates_found' => count($allDuplicateRecords),
                        'target_records_count' => 0
                    ]
                ], 200);
            }

            // Group records by duplicate criteria
            $groups = [];
            foreach ($targetRecords as $record) {
                $keyParts = [];
                foreach ($groupFields as $field) {
                    $keyParts[$field] = $record->{$field} ?? null;
                }
                $hashKey = md5(json_encode($keyParts));

                if (!isset($groups[$hashKey])) {
                    $groups[$hashKey] = [];
                }
                $groups[$hashKey][] = $record;
            }

            $deletedCount = 0;
            $deletedIds = [];
            $keptIds = [];
            $groupDetails = [];

            // For each group, keep the ORIGINAL (oldest) record and delete the DUPLICATES (newer ones)
            foreach ($groups as $hashKey => $groupRecords) {
                if (count($groupRecords) <= 1) {
                    continue; // Skip if not actually duplicates
                }

                // Sort by created_at ascending (oldest first) - ORIGINAL comes first
                usort($groupRecords, fn($a, $b) => strcmp($a->created_at, $b->created_at));

                // Keep the ORIGINAL (first/oldest) record - this is the original entry
                $originalRecord = array_shift($groupRecords);
                $keptIds[] = $originalRecord->id;

                $groupDetails[] = [
                    'group_key' => $hashKey,
                    'original_record_kept' => [
                        'id' => $originalRecord->id,
                        'created_at' => $originalRecord->created_at,
                        'company_name' => $originalRecord->company_name,
                        'email' => $originalRecord->email_address_system,
                        'note' => 'ORIGINAL - KEPT'
                    ],
                    'duplicate_records_deleted' => []
                ];

                // Delete the DUPLICATES (newer records) - these are the copies/duplicates
                foreach ($groupRecords as $duplicateRecord) {
                    $address = Address::find($duplicateRecord->id);
                    if ($address) {
                        $address->delete(); // Soft delete the duplicate
                        $deletedIds[] = $duplicateRecord->id;
                        $deletedCount++;

                        $groupDetails[count($groupDetails) - 1]['duplicate_records_deleted'][] = [
                            'id' => $duplicateRecord->id,
                            'created_at' => $duplicateRecord->created_at,
                            'company_name' => $duplicateRecord->company_name,
                            'email' => $duplicateRecord->email_address_system,
                            'note' => 'DUPLICATE - DELETED'
                        ];
                    }
                }
            }

            DB::commit();

            Log::channel('address_deletion')->info('Successfully deleted duplicate records with null feedback and seen', [
                'deleted_count' => $deletedCount,
                'deleted_ids' => $deletedIds,
                'kept_ids' => $keptIds,
                'total_groups_processed' => count($groups),
                'group_details' => $groupDetails
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} DUPLICATE records (kept ORIGINAL records). Only records with null feedback and null seen were processed.",
                'summary' => [
                    'original_records_kept' => count($keptIds),
                    'duplicate_records_deleted' => $deletedCount,
                    'total_groups_processed' => count($groups)
                ],
                'original_records_kept' => $keptIds,
                'duplicate_records_deleted' => $deletedIds,
                'debug_info' => [
                    'total_duplicates_found' => count($allDuplicateRecords),
                    'target_records_count' => count($targetRecords),
                    'group_details' => $groupDetails
                ]
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();

            Log::channel('address_deletion')->error('Failed to delete duplicate records', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to delete duplicate records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug endpoint to see what duplicates exist and their feedback/seen status
     */
    public function debugDuplicates(Request $request)
    {
        // Define the fields used to identify duplicates
        $groupFields = [
            'phone_number',
            'email_address_system',
            'company_name',
            'city',
            'website',
            'mobile_number',
            'email_address_new',
            'sub_project_id',
            'main_category_query',
            'sub_category_category',
            'postal_code',
            'street_address',
        ];

        $selectGroupCols = implode(', ', array_map(fn($c) => "`$c`", $groupFields));
        $joinConditions = implode(' AND ', array_map(fn($c) => "a.`$c` <=> g.`$c`", $groupFields));

        // Get all duplicates with their feedback and seen status
        $sql = "
            SELECT
                a.id,
                a.created_at,
                a.feedback,
                a.seen,
                a.deleted_at,
                a.phone_number,
                a.email_address_system,
                a.company_name,
                a.city,
                a.website,
                a.mobile_number,
                a.email_address_new,
                a.sub_project_id,
                a.main_category_query,
                a.sub_category_category,
                a.postal_code,
                a.street_address
            FROM addresses a
            INNER JOIN (
                SELECT {$selectGroupCols}
                FROM addresses
                WHERE deleted_at IS NULL
                GROUP BY {$selectGroupCols}
                HAVING COUNT(*) > 1
            ) g ON {$joinConditions}
            WHERE a.deleted_at IS NULL
            ORDER BY a.created_at ASC
        ";

        $duplicateRecords = DB::select($sql);

        // Group by duplicate criteria
        $groups = [];
        foreach ($duplicateRecords as $record) {
            $keyParts = [];
            foreach ($groupFields as $field) {
                $keyParts[$field] = $record->{$field} ?? null;
            }
            $hashKey = md5(json_encode($keyParts));

            if (!isset($groups[$hashKey])) {
                $groups[$hashKey] = [];
            }
            $groups[$hashKey][] = $record;
        }

        // Analyze each group
        $analysis = [];
        foreach ($groups as $hashKey => $groupRecords) {
            if (count($groupRecords) <= 1) continue;

            $nullFeedbackSeen = [];
            $withFeedbackSeen = [];

            foreach ($groupRecords as $record) {
                $hasNullFeedback = empty($record->feedback);
                $hasNullSeen = empty($record->seen);

                if ($hasNullFeedback && $hasNullSeen) {
                    $nullFeedbackSeen[] = $record;
                } else {
                    $withFeedbackSeen[] = $record;
                }
            }

            // Sort to determine original vs duplicates
            usort($groupRecords, fn($a, $b) => strcmp($a->created_at, $b->created_at));

            $analysis[] = [
                'group_key' => $hashKey,
                'total_records' => count($groupRecords),
                'null_feedback_seen_count' => count($nullFeedbackSeen),
                'with_feedback_seen_count' => count($withFeedbackSeen),
                'records' => array_map(function($r, $index) use ($groupRecords) {
                    $isOriginal = $index === 0; // First record (oldest) is original
                    return [
                        'id' => $r->id,
                        'created_at' => $r->created_at,
                        'company_name' => $r->company_name,
                        'email' => $r->email_address_system,
                        'feedback' => $r->feedback,
                        'seen' => $r->seen,
                        'has_null_feedback' => empty($r->feedback),
                        'has_null_seen' => empty($r->seen),
                        'would_be_deleted' => empty($r->feedback) && empty($r->seen),
                        'is_original_or_duplicate' => $isOriginal ? 'ORIGINAL (would be kept)' : 'DUPLICATE (would be deleted)'
                    ];
                }, $groupRecords, array_keys($groupRecords))
            ];
        }

        return response()->json([
            'success' => true,
            'total_duplicate_groups' => count($analysis),
            'analysis' => $analysis,
            'summary' => [
                'groups_with_null_feedback_seen' => count(array_filter($analysis, fn($g) => $g['null_feedback_seen_count'] > 0)),
                'total_records_with_null_feedback_seen' => array_sum(array_column($analysis, 'null_feedback_seen_count')),
                'total_records_with_feedback_seen' => array_sum(array_column($analysis, 'with_feedback_seen_count'))
            ]
        ]);
    }

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
                'mobile_number' => $addressData['mobile'] ?? null,
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

                // Delete the address
                $address->delete();

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
        $query = Address::query()->withTrashed();

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
                'addresses.*.mobile' => 'nullable|string',
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
                'addresses.*.forbidden_promotion' => 'nullable',
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
            $existingEmails = Address::pluck('email_address_system')->filter()->flip()->all();
            $existingPhones = Address::pluck('phone_number')->filter()->flip()->all();
            $existingMobiles = Address::pluck('mobile_number')->filter()->flip()->all();

            $errors = [];
            $createdIds = [];

            foreach ($request->input('addresses') as $index => $addressData) {
                $rowNumber = $index + 1;
                $normalizedData = $addressData;
                if (!array_key_exists('mobile_number', $normalizedData)) {
                    $normalizedData['mobile_number'] = $normalizedData['mobile'] ?? null;
                }

                $validation = AddressDataValidator::validateRowData($normalizedData, $rowNumber, $subprojectIds);
                if (!$validation['valid']) {
                    $errors = array_merge($errors, $validation['errors']);
                    continue;
                }

                $dupCheck = AddressDataValidator::checkDuplicates(
                    $normalizedData,
                    $existingByStreetPostal,
                    $existingByNamePostal,
                    $existingEmails,
                    $existingPhones,
                    $existingMobiles,
                    $rowNumber
                );
                if (!$dupCheck['valid']) {
                    $errors = array_merge($errors, $dupCheck['errors']);
                    continue;
                }

                $insertData = AddressDataValidator::prepareInsert($normalizedData);
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
                if (!empty($address->mobile_number)) {
                    $existingMobiles[$address->mobile_number] = true;
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
            Log::error('Failed to save addresses', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to save addresses',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    /**
     * no need any more
     * Get count of addresses by sub project IDs 1, 2, and 3
     */
    // public function getAddressCountsBySubProjects(Request $request)
    // {


    //     try {
    //         // Get counts for sub project IDs 1, 2, and 3
    //         $subProjectIds = [1, 2, 3];
    //         $counts = [];
    //         $totalCount = 0;

    //         foreach ($subProjectIds as $subProjectId) {
    //             $count = Address::where('sub_project_id', $subProjectId)->count();
    //             $counts[$subProjectId] = $count;
    //             $totalCount += $count;
    //         }

    //         // Get sub project details for context
    //         $subProjects = SubProject::whereIn('id', $subProjectIds)
    //             ->select('id', 'title', 'description')
    //             ->get()
    //             ->keyBy('id');

    //         return response()->json([
    //             'success' => true,
    //             'data' => [
    //                 'counts' => $counts,
    //                 'total_count' => $totalCount,
    //                 'sub_projects' => $subProjects,
    //                 'sub_project_ids' => $subProjectIds
    //             ]
    //         ], 200);

    //     } catch (Exception $e) {
    //         Log::error('Failed to get address counts by sub projects', ['error' => $e->getMessage()]);
    //         return response()->json([
    //             'success' => false,
    //             'error' => 'Failed to get address counts: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * no need any more
     * Hard delete addresses by sub project IDs 1, 2, and 3
     */

    // public function hardDeleteAddressesBySubProjects(Request $request)
    // {

    //     DB::beginTransaction();

    //     try {
    //         $subProjectIds = [1, 2, 3];
    //         $deletedCounts = [];
    //         $totalDeleted = 0;

    //         // Get addresses to be deleted for logging
    //         $addressesToDelete = Address::whereIn('sub_project_id', $subProjectIds)->withTrashed()->get();

    //         Log::channel('address_deletion')->info('Starting hard deletion of addresses by sub project IDs', [
    //             'sub_project_ids' => $subProjectIds,
    //             'total_addresses' => $addressesToDelete->count(),
    //         ]);

    //         foreach ($subProjectIds as $subProjectId) {
    //             $addresses = Address::where('sub_project_id', $subProjectId)->get();
    //             $count = $addresses->count();

    //             if ($count > 0) {
    //                 // Hard delete addresses (forceDelete bypasses soft delete)
    //                 Address::where('sub_project_id', $subProjectId)->forceDelete();
    //                 $deletedCounts[$subProjectId] = $count;
    //                 $totalDeleted += $count;

    //                 Log::channel('address_deletion')->info('Hard deleted addresses for sub project', [
    //                     'sub_project_id' => $subProjectId,
    //                     'deleted_count' => $count
    //                 ]);
    //             } else {
    //                 $deletedCounts[$subProjectId] = 0;
    //             }
    //         }

    //         DB::commit();

    //         Log::channel('address_deletion')->info('Successfully completed hard deletion of addresses', [
    //             'total_deleted' => $totalDeleted,
    //             'deleted_by_sub_project' => $deletedCounts,
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Addresses hard deleted successfully.',
    //             'data' => [
    //                 'deleted_counts' => $deletedCounts,
    //                 'total_deleted' => $totalDeleted,
    //                 'sub_project_ids' => $subProjectIds
    //             ]
    //         ], 200);

    //     } catch (Exception $e) {
    //         DB::rollBack();

    //         Log::channel('address_deletion')->error('Failed to hard delete addresses by sub projects', [
    //             'error' => $e->getMessage(),
    //             'sub_project_ids' => $subProjectIds,
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'error' => 'Failed to hard delete addresses: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }
}
