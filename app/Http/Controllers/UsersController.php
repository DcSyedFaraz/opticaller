<?php

namespace App\Http\Controllers;

use App\Mail\AccountCreated;
use App\Models\Activity;
use App\Models\Address;
use App\Models\GlobalLockedFields;
use App\Models\SubProject;
use App\Models\User;
use App\Services\AddressService;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Mail;
use Redirect;
use Session;
use Spatie\Permission\Models\Role;
use Str;

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
    public function change_password_page()
    {
        if (Session::get('otp_login_email')) {
            // Retrieve the user using the email stored in the session
            $user = User::where('email', Session::get('otp_login_email'))->first();

            if ($user) {

                return Inertia::render('ChangePassword', [
                    'message' => 'Please change your password.'
                ]);
            }
        }
        return redirect()->route('login')->with('message', 'Login to your account.');
    }
    public function change_password(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check if the user is logging in with OTP (session check)
        if ($request->session()->get('otp_login_email')) {
            // Retrieve the user using the email stored in the session
            $user = User::where('email', $request->session()->get('otp_login_email'))->first();

            if ($user) {
                // Update the user's password
                $user->password = Hash::make($request->password);
                $user->otp = null; // Remove the OTP after successful password change
                $user->save();

                // Forget the OTP login email from the session
                $request->session()->forget('otp_login_email');
                // Return a success response
                return response()->json(['message', 'Password updated successfully']);
            }

            // Return error if the user is not found
        }
        return response()->json(['message', 'User not found'], 404);
    }

    public function toggleStatus(Request $request)
    {
        // dd($request->all());
        $user = User::findOrFail($request->id);
        $user->is_active = !$user->is_active;
        $user->save();

        return Redirect::back()->with('message', 'User status updated successfully!');
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
        // Validate the input data
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required',
            'auto_calling' => 'nullable|boolean',
        ]);

        // Generate OTP if no password is provided
        $otp = Str::random(8);
        // dd($data['password'] ? null : $otp);
        // Create the user and set the necessary fields
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'auto_calling' => $data['auto_calling'] ?? false,
            'password' => $data['password'] ? bcrypt($data['password']) : bcrypt($otp),
            'otp' => $data['password'] ? null : $otp,
        ];

        $user = User::create($userData);

        // Assign the roles to the user
        $user->syncRoles($data['roles']);

        // Send OTP email if password is not provided
        if ($data['password'] === null) {
            Mail::to($user->email)->send(new AccountCreated($user, $otp));
        }

        // Redirect back with a success message
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
            'email' => "required|string|email|max:255|unique:users,email,{$user->id}",
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required',
            'auto_calling' => 'nullable|boolean',
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'auto_calling' => $data['auto_calling'] ?? false,
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
