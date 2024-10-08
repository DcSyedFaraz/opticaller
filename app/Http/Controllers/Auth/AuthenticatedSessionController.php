<?php

namespace App\Http\Controllers\Auth;

use App\Events\UserStatusChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();

        // Check if the user is inactive
        if ($user && !$user->is_active) {

            return redirect()->back()->with('message', 'Yours account is inactive.');

        }

        $request->authenticate();

        $request->session()->regenerate();
        Auth::user()->logintime()->create(['login_time' => now()]);

        $user = Auth::guard()->user()->getRoleNames();


        return match ($user[0]) {
            'admin' => redirect()->route('dashboard'),
            default => redirect()->route('dash'),
        };
        // return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $lastLogin = Auth::user()->logintime()->orderBy('id', 'desc')->first();

        if ($lastLogin) {
            $lastLogin->update(['logout_time' => now()]);
            broadcast(new UserStatusChanged(Auth::id(), 'offline'));
        }
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
