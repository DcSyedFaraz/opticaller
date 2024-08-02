<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Project;
use App\Models\User;
use DB;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $query = Address::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('company_name', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%")
                ->orWhere('email_address', 'like', "%{$search}%");
        }

        // Sorting functionality with validation
        $sortField = $request->input('sortField', 'id');
        $sortOrder = $request->input('sortOrder', 'asc');
        // dd($sortField, $sortOrder);
        $query->orderBy($sortField, $sortOrder);

        // Pagination
        $addresses = $query->paginate(10);

        return inertia('Addresses/Index', [
            'addresses' => $addresses,
            'filters' => $request->all('search', 'sortField', 'sortOrder', 'page')
        ]);
    }


    public function show(Address $address)
    {
        $project = Project::all();
        $users = User::select('id', 'name')->get();
        return inertia('Addresses/Show', ['address' => $address, 'projects' => $project, 'users' => $users]);
    }
    public function create()
    {
        $project = Project::all();
        $users = User::select('id', 'name')->get();
        // dd($users);
        return inertia('Addresses/Create', ['projects' => $project, 'users' => $users]);
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
            'email_address' => 'nullable|email|max:255',
            'personal_notes' => 'nullable|string',
            'interest_notes' => 'nullable|string',
            'feedback' => 'nullable|string|in:Not Interested,Interested,Request,Follow-up,Delete Address',
            'follow_up_date' => 'nullable|date',
            'project_id' => 'nullable|exists:projects,id',
            'user_id' => 'nullable|exists:users,id',
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
            'email_address' => 'nullable|email|max:255',
            'personal_notes' => 'nullable|string',
            'interest_notes' => 'nullable|string',
            'feedback' => 'nullable|string|in:Not Interested,Interested,Request,Follow-up,Delete Address',
            'follow_up_date' => 'nullable|date',
            'project_id' => 'nullable|exists:projects,id',
            'user_id' => 'nullable|exists:users,id',
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
    public function nextAddress()
    {
        $address = Address::where('user_id', auth()->id())
            ->whereNull('feedback')
            ->orderBy('id')
            ->first();

        if ($address) {
            return redirect()->route('addresses.show', $address);
        }

        return redirect()->route('addresses.index')->with('message', 'No more addresses to display.');
    }
}
