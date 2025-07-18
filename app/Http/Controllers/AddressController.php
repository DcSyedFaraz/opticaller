<?php

namespace App\Http\Controllers;

use App\Mail\CallbackMail;
use App\Models\Address;
use App\Models\GlobalLockedFields;
use App\Models\SubProject;
use App\Models\User;
use Arr;
use DB;
use Http;
use Illuminate\Http\Request;
use Log;
use Mail;

class AddressController extends Controller
{
    public function search(Request $request)
    {
        $request->validate([
            'sub_project_id' => 'required|integer',
            'contact_id' => 'nullable|string',
            'name' => 'nullable|string',
        ]);

        $sub_project_id = $request->input('sub_project_id');
        $contact_id = $request->input('contact_id');
        $name = $request->input('name');

        if (!$contact_id && !$name) {
            return response()->json([
                'error' => 'Contact ID or name is required.'
            ], 422);
        }

        $query = Address::with(['calLogs.notes', 'subproject.projects', 'subproject.feedbacks', 'calLogs.users'])
            ->where('sub_project_id', $sub_project_id);

        if ($contact_id) {
            $query->where('contact_id', $contact_id);
        } elseif ($name) {
            $query->where(function ($q) use ($name) {
                $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$name}%"])
                    ->orWhere('company_name', 'like', "%{$name}%");
            });
        }

        $address = $query->first();

        if ($address) {
            $address->seen = now();
            $address->save();
            $globalLockedFields = GlobalLockedFields::firstOrCreate()->locked_fields;

            return response()->json([
                'address' => $address,
                'lockfields' => $globalLockedFields,
            ]);
        }

        return response()->json([
            'error' => 'Address not found for the provided criteria.',
        ], 404);
    }
    public function index(Request $request)
    {
        $query = Address::query();

        /* ---------------------------------
        | 1.  Forbidden Promotion Filter
        |---------------------------------*/
        $showForbiddenPromotion = $request->boolean('showForbiddenPromotion', false);
        $query->where('forbidden_promotion', $showForbiddenPromotion);

        /* ---------------------------------
         | 1.  Row filters sent as JSON
         |---------------------------------*/
        $filters = json_decode($request->input('filters', '{}'), true);

        // Global filter
        if (!empty($filters['global']['value'])) {
            $g = $filters['global']['value'];
            $query->where(function ($q) use ($g) {
                $q->where('addresses.company_name', 'like', "%{$g}%")
                    ->orWhere('addresses.contact_id', 'like', "%{$g}%")
                    ->orWhere('addresses.feedback', 'like', "%{$g}%")
                    ->orWhere('addresses.email_address_system', 'like', "%{$g}%")
                    ->orWhere('addresses.deal_id', 'like', "%{$g}%");
            });
        }

        // Per-column filters
        $columnMap = [
            'company_name' => 'addresses.company_name',
            'subproject.title' => 'subprojects.title',
            'email_address_system' => 'addresses.email_address_system',
            'feedback' => 'addresses.feedback',
            'follow_up_date' => 'addresses.follow_up_date',
            'deal_id' => 'addresses.deal_id',
            'contact_id' => 'addresses.contact_id',
            'closure_user_name' => 'users.name'
        ];

        foreach ($filters ?? [] as $field => $filter) {
            $value = $filter['value'] ?? null;
            if ($field === 'global' || $value === null || $value === '') {
                continue;
            }

            // join for subproject / user name filters
            if ($field === 'subproject.title') {
                $query->whereHas('subproject', fn($q) => $q->where('title', 'like', "%{$value}%"));
            } elseif ($field === 'closure_user_name') {
                $query->whereHas('lastuser.users', fn($q) => $q->where('name', 'like', "%{$value}%"));
            } else {
                $query->where($columnMap[$field], 'like', "%{$value}%");
            }
        }

        /* ---------------------------------
         | 2.  Sorting
         |---------------------------------*/
        $sortField = $request->input('sortField', 'id');
        $sortOrder = $request->input('sortOrder', 'desc');

        if ($sortField === 'closure_user_name') {
            $query->leftJoin('activities', function ($join) {
                $join->on('addresses.id', '=', 'activities.address_id')
                    ->where('activities.activity_type', 'call');
            })
                ->leftJoin('users', 'activities.user_id', '=', 'users.id')
                ->select('addresses.*', 'users.name as closure_user_name')
                ->orderBy('users.name', $sortOrder);
        } else {
            $query->orderBy($sortField, $sortOrder);
        }

        /* ---------------------------------
         | 3.  Pagination & eager loads
         |---------------------------------*/
        $addresses = $query->with('subproject', 'lastuser.users')
            ->paginate(10)
            ->appends($request->except('page')); // keep query-string tidy
        // dd($addresses);
        return inertia('Addresses/Index', [
            'addresses' => $addresses,
            'showForbiddenPromotion' => $showForbiddenPromotion,
            'filters' => $request->all('sortField', 'sortOrder', 'page')
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
        // dd($request->all());
        // Validation rules
        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255',
            'salutation' => 'nullable|string|max:255',
            'forbidden_promotion' => 'nullable',
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
            'contact_id' => "nullable|string",
            // 'contact_id' => "nullable|string|unique:addresses,contact_id,$id",
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
            'forbidden_promotion' => 'nullable',
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
