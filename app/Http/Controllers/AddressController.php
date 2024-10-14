<?php

namespace App\Http\Controllers;

use App\Mail\CallbackMail;
use App\Models\Address;
use App\Models\GlobalLockedFields;
use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use DB;
use Http;
use Illuminate\Http\Request;
use Log;
use Mail;

class AddressController extends Controller
{
    public function getAddressByContactId($contact_id, $sub_project_id)
    {
        // Fetch the address using the Contact ID
        $address = Address::with(['calLogs.notes', 'subproject.projects', 'subproject.feedbacks', 'calLogs.users'])->where('contact_id', $contact_id)->where('sub_project_id', $sub_project_id)->first();

        if ($address) {
            $address->seen = now();
            $address->save();
            // Assuming you have lockfields logic
            $globalLockedFields = GlobalLockedFields::firstOrCreate()->locked_fields;

            return response()->json([
                'address' => $address,
                'lockfields' => $globalLockedFields,
            ]);
        } else {
            return response()->json([
                'error' => 'Address not found for the provided Contact ID.',
            ], 404);
        }
    }
    public function index(Request $request)
    {
        $query = Address::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('addresses.company_name', 'like', "%{$search}%")
                ->orWhere('addresses.contact_id', 'like', "%{$search}%")
                ->orWhere('addresses.feedback', 'like', "%{$search}%")
                ->orWhere('addresses.email_address_system', 'like', "%{$search}%")
                ->orWhere('addresses.deal_id', 'like', "%{$search}%");
        }

        // Sorting functionality with validation
        $sortField = $request->input('sortField', 'id');
        $sortOrder = $request->input('sortOrder', 'desc');
        // $query->orderBy($sortField, $sortOrder);
        if ($sortField === 'closure_user_name') {
            // dd($sortField, $sortOrder);
            // Join the users table to sort by users.name
            $query->leftJoin('activities', function ($join) {
                $join->on('addresses.id', '=', 'activities.address_id')
                    ->where('activities.activity_type', 'call');
            })
                ->leftJoin('users', 'activities.user_id', '=', 'users.id')
                ->select('addresses.*', 'users.name as closure_user_name')
                ->orderBy('users.name', $sortOrder);

        } else {
            // Default sorting
            $query->orderBy($sortField, $sortOrder);
        }

        // Pagination
        $addresses = $query->with('subproject', 'lastuser.users')->paginate(10);

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
            'website' => 'nullable|max:255',
            'phone_number' => 'nullable|string|max:20',
            'email_address_system' => 'required|email|max:255',
            'email_address_new' => 'nullable|email|max:255',
            'sub_project_id' => 'nullable|exists:sub_projects,id',
            'hubspot_tag' => 'nullable|string|max:255',
            'deal_id' => 'nullable|string|max:255',
            'company_id' => 'nullable|string|max:255',
            'titel' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:255',
            'contact_id' => 'nullable|string|unique:addresses,contact_id,' . $id,
        ]);

        DB::beginTransaction();

        try {
            $address = Address::findOrFail($id);

            $address->update($validatedData);

            DB::commit();

            return redirect()->route('addresses.index')->with('message', 'Address updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Address update failed', ['error' => $e->getMessage()]);

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
            'website' => 'nullable|max:255',
            'phone_number' => 'nullable|string|max:20',
            'email_address_system' => 'required|email|max:255',
            'email_address_new' => 'nullable|email|max:255',
            'sub_project_id' => 'nullable|exists:sub_projects,id',
            'hubspot_tag' => 'nullable|string|max:255',
            'deal_id' => 'nullable|string|max:255',
            'company_id' => 'nullable|string|max:255',
            'titel' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:255',
            'contact_id' => 'nullable|string|unique:addresses,contact_id',
        ]);

        DB::beginTransaction();


        try {
            Address::create($validatedData);


            DB::commit();

            return redirect()->route('addresses.index')->with('message', 'Address added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Address creation failed', ['error' => $e->getMessage()]);

            return redirect()->back()->withErrors(['error' => 'Address creation failed: ' . $e->getMessage()]);
        }
    }
    public function callback()
    {
        return inertia('Addresses/callBack');
    }
    public function callbackMail(Request $request)
    {
        // dd('d');
        // Validate the incoming request
        $validatedData = $request->validate([
            'project' => 'required',
            'salutation' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
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


        // Determine the email recipient based on the project field
        $emailRecipient = '';
        switch ($validatedData['project']) {
            case 'Vimtronix':
                $emailRecipient = 'info@vimtronix.com';
                break;
            case 'XSimpress':
                $emailRecipient = 'info@xsimpress.com';
                break;
            case 'Box4Pflege':
                $emailRecipient = 'info@box4pflege.de';
                break;
            case 'Management':
                $emailRecipient = 'geschaeftsleitung@vim-solution.com';
                break;
            case 'MEDIQANO':
                $emailRecipient = 'info@mediqano.com';
                break;
            default:
                // You can handle other cases or throw an exception if necessary
                Log::error('Unknown project: ' . $validatedData['project']);
                break;
        }
        $details = [
            'senderName' => $user->name,
            'senderId' => $user->id,
            'salutation' => $validatedData['salutation'],
            'company' => $validatedData['company'] ?? null,
            'project' => $emailRecipient,
            // 'project' => 'dcsyedfaraz@gmail.com',
            'project_name' => $validatedData['project'],
            'firstName' => $validatedData['firstName'],
            'lastName' => $validatedData['lastName'],
            'phoneNumber' => $validatedData['phoneNumber'],
            'notes' => $validatedData['notes'],
        ];
        // dd($details);
        // testing hook
        // $webhookUrl = 'https://hook.eu1.make.com/9tjpua1qx1dhgil7zbisfhaucr11hqge';

        // live hook
        $webhookUrl = 'https://hook.eu1.make.com/nnhsxiekkqv73s25g1em9p09s3itywou';
        // Call webhook
        $response = Http::post($webhookUrl, $details);
        if ($response->successful()) {
            Log::info('Webhook called: ' . $response->body());
        } else {
            // Handle failure (log the error, retry, etc.)
            Log::error('Webhook call failed: ' . $response->body());
        }

        // Send the email if a recipient was set
        if ($emailRecipient) {
            Mail::to($emailRecipient)->bcc('arsalan195@gmail.com')->send(new CallbackMail($details));
        } else {
            Log::error('No email recipient was set for the project.');
        }


        // Return inertia response
        return redirect()->route('dash')->with('message', 'Call back sent successfully!');

    }
    public function destroy(Address $address)
    {
        $address->delete();

        return redirect()->route('addresses.index')->with('message', 'Address deleted successfully.');
    }
}
