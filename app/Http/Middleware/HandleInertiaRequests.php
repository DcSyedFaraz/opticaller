<?php

namespace App\Http\Middleware;

use Auth;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = Auth::user();
        $logintime = $user?->logintimes()->login_time ?? '';
        $role = $user?->getRoleNames();
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'logintime' => $logintime,
                'roles' => $role,
            ],
            'flash' => [
                'message' => $request->session()->get('message'),
                'imported' => $request->session()->get('imported'),
                'skipped' => $request->session()->get('skipped'),
                'importErrors' => $request->session()->get('importErrors'),
                'total' => $request->session()->get('total'),
            ],
        ];
    }
}
