<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Address;
use App\Models\GlobalLockedFields;
use App\Models\SubProject;
use App\Models\User;
use App\Services\AddressService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Redirect;
use Session;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function toggleStatusget()
    {
        $users = User::with('latestLoginTime', 'latestLogoutTime')->get();
        // dd($users);
        return Inertia::render('Settings/UserStatusToggle', [
            'users' => $users
        ]);
    }

    public function toggleStatus(Request $request)
    {
        // dd($request->all());
        $user = User::findOrFail($request->id);
        $user->is_active = !$user->is_active;
        $user->save();

        return Redirect::back()->with('message', 'Userss status updated successfully!');
    }

    public function dash()
    {
        $addressService = new AddressService();
        $address = $addressService->getDueAddress();
        $globalLockedFields = GlobalLockedFields::firstOrCreate()->locked_fields;
        $subproject = SubProject::select('id', 'title')->get();

        // Return the address with Inertia
        return Inertia::render('Users/AddressDash', ['address' => $address, 'subproject' => $subproject, 'lockfields' => $globalLockedFields]);
    }

    public function index(Request $request)
    {
        $query = User::with('roles');

        // Search functionality
        if ($request->has('search') && $request->input('search') !== '') {
            $search = $request->input('search');
            $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%");
            // dd($query);
        }

        // Sorting functionality
        if (
            $request->has('sortField') && $request->input('sortField') !== '' &&
            $request->has('sortOrder') && $request->input('sortOrder') !== null
        ) {
            $sortField = $request->input('sortField');
            $sortOrder = $request->input('sortOrder') === 1 ? 'asc' : 'desc';
            $query->orderBy($sortField, $sortOrder);
        }


        // Pagination
        $users = $query->paginate(10);
        // dd($users);
        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'sortField', 'sortOrder'])
        ]);
    }


    public function show()
    {
        $users = User::with('roles')->get();
        return Inertia::render('Users/Index', ['users' => $users]);
    }

    public function create()
    {
        $roles = Role::all();
        return Inertia::render('Users/Create', ['roles' => $roles]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required'
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $user->syncRoles($data['roles']);

        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->first()->name;
        return Inertia::render('Users/Edit', ['user' => $user, 'roles' => $roles, 'userRoles' => $userRoles]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required'
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'] ? bcrypt($data['password']) : $user->password,
        ]);

        $user->syncRoles($data['roles']);

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully');
    }
}
