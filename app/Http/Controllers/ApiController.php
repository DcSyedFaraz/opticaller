<?php

namespace App\Http\Controllers;

use App\Models\Address;
use DB;
use Exception;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function index()
    {
        $addresses = Address::all()->makeHidden(['created_at', 'updated_at', 'deleted_at', 'seen']);
        return response()->json($addresses);
    }
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validate incoming request
            $validatedData = $request->validate([
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
                'addresses.*.sub_project_id' => 'required|integer|exists:sub_projects,id',
                'addresses.*.priority' => 'nullable|integer',
                'addresses.*.seen' => 'nullable|integer',
            ]);

            // Loop through each address and save it
            foreach ($validatedData['addresses'] as $addressData) {
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
