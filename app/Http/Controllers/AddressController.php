<?php

namespace App\Http\Controllers;

use App\Mail\CallbackMail;
use App\Models\Address;
use App\Models\GlobalLockedFields;
use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Mail;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $query = Address::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('company_name', 'like', "%{$search}%")
                ->orWhere('feedback', 'like', "%{$search}%")
                ->orWhere('email_address_system', 'like', "%{$search}%")
                ->orWhere('deal_id', 'like', "%{$search}%");
        }

        // Sorting functionality with validation
        $sortField = $request->input('sortField', 'id');
        $sortOrder = $request->input('sortOrder', 'asc');
        // dd($sortField, $sortOrder);
        $query->orderBy($sortField, $sortOrder);

        // Pagination
        $addresses = $query->with('subproject','lastuser.users')->paginate(10);

        return inertia('Addresses/Index', [
            'addresses' => $addresses,
            'filters' => $request->all('search', 'sortField', 'sortOrder', 'page')
        ]);
    }


    public function show(Address $address)
    {
        $subproject = SubProject::all();
        $users = User::select('id', 'name')->get();
        return inertia('Addresses/Show', ['address' => $address, 'subprojects' => $subproject, 'users' => $users]);
    }
    public function create()
    {
        $subproject = SubProject::all();
        $users = User::select('id', 'name')->get();
        // dd($users);
        return inertia('Addresses/Create', ['subprojects' => $subproject, 'users' => $users]);
    }
    public function updateLockedFields(Request $request)
    {
        $validated = $request->validate([
            'locked_fields' => 'array',
            'locked_fields.*' => 'string|in:company_name,salutation,first_name,last_name,street_address,postal_code,city,website,phone_number,email_address_new,country,contact_id',
        ]);

        $globalLockedFields = GlobalLockedFields::firstOrCreate();
        $globalLockedFields->update(['locked_fields' => $validated['locked_fields']]);

        return redirect()->back()->with('success', 'Locked fields updated successfully.');
    }

    public function update(Request $request, $id)
    {
        // Validation rules
        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255',
            'salutation' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'street_address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'phone_number' => 'nullable|string|max:20',
            'email_address_system' => 'required|email|max:255',
            'email_address_new' => 'required|email|max:255',
            'feedback' => 'nullable|string|in:Not Interested,Interested,Request,Follow-up,Delete Address',
            'follow_up_date' => 'nullable|date',
            'sub_project_id' => 'nullable|exists:sub_projects,id',
        ]);

        DB::beginTransaction();

        try {
            $address = Address::findOrFail($id);

            $address->update($validatedData);

            DB::commit();

            return redirect()->route('addresses.index')->with('message', 'Address updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Address update failed', ['error' => $e->getMessage()]);

            return redirect()->back()->withErrors(['error' => 'Address update failed: ' . $e->getMessage()]);
        }
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255',
            'salutation' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'street_address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'phone_number' => 'nullable|string|max:20',
            'email_address_system' => 'required|email|max:255',
            'email_address_new' => 'required|email|max:255',
            'feedback' => 'nullable|string|in:Not Interested,Interested,Request,Follow-up,Delete Address',
            'follow_up_date' => 'nullable|date',
            'sub_project_id' => 'nullable|exists:sub_projects,id',
        ]);

        DB::beginTransaction();


        try {
            Address::create($validatedData);


            DB::commit();

            return redirect()->route('addresses.index')->with('message', 'Address added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Address creation failed', ['error' => $e->getMessage()]);

            return redirect()->back()->withErrors(['error' => 'Address creation failed: ' . $e->getMessage()]);
        }
    }
    public function callback()
    {
        return inertia('Addresses/callBack');
    }
    public function callbackMail(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'project' => 'required|email',
            'salutation' => 'required|string|max:255',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'phoneNumber' => 'required|string|max:20',
            'notes' => 'required|string',
        ]);

        $address = new \App\Models\CallbackMail();
        $address->project = $validatedData['project'];
        $address->salutation = $validatedData['salutation'];
        $address->first_name = $validatedData['firstName'];
        $address->last_name = $validatedData['lastName'];
        $address->phone_number = $validatedData['phoneNumber'];
        $address->notes = $validatedData['notes'];
        $address->user_id = auth()->id();
        $address->save();

        $user = auth()->user();
        $details = [
            'senderName' => $user->name,
            'senderId' => $user->id,
            'salutation' => $validatedData['salutation'],
            'firstName' => $validatedData['firstName'],
            'lastName' => $validatedData['lastName'],
            'phoneNumber' => $validatedData['phoneNumber'],
            'notes' => $validatedData['notes'],
        ];

        Mail::to($validatedData['project'])->bcc('arsalan195@gmail.com')->send(new CallbackMail($details));

        // Return inertia response
        return redirect()->route('dash')->with('message', 'Call back sent successfully!');

    }

}
