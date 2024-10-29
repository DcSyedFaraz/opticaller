<?php

namespace App\Http\Controllers;

use Exception;
use Http;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Twilio\Exceptions\RestException;
use Twilio\Rest\Client;

class WebexController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Users/call', [
            'webexAuthenticated' => session()->has('webex_access_token'),
            'webexAccessToken' => session('webex_access_token'),
        ]);
    }
    public function refreshToken()
    {
        $refreshToken = session('webex_refresh_token');

        if (!$refreshToken) {
            return response()->json(['error' => 'No refresh token available'], 401);
        }

        $response = Http::asForm()->post('https://webexapis.com/v1/access_token', [
            'grant_type' => 'refresh_token',
            'client_id' => config('services.webex.client_id'),
            'client_secret' => config('services.webex.client_secret'),
            'refresh_token' => $refreshToken,
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to refresh token'], 500);
        }

        $data = $response->json();

        // Update session with new tokens
        session([
            'webex_access_token' => $data['access_token'],
            'webex_refresh_token' => $data['refresh_token'],
            'webex_token_expires' => now()->addSeconds($data['expires_in']),
        ]);

        return response()->json(['message' => 'Token refreshed']);
    }

    public function redirectToWebex()
    {
        $clientId = config('services.webex.client_id');
        $redirectUri = config('services.webex.redirect');
        $scope = 'spark:all';
        $responseType = 'code';
        $state = csrf_token();

        $url = 'https://webexapis.com/v1/authorize?' . http_build_query([
            'client_id' => $clientId,
            'response_type' => $responseType,
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
            'state' => $state,
        ]);

        return redirect($url);
    }

    public function handleWebexCallback(Request $request)
    {
        $code = $request->input('code');
        $state = $request->input('state');

        if ($state !== $request->session()->token()) {
            abort(403, 'Invalid state parameter');
        }

        $response = Http::asForm()->post('https://webexapis.com/v1/access_token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.webex.client_id'),
            'client_secret' => config('services.webex.client_secret'),
            'code' => $code,
            'redirect_uri' => config('services.webex.redirect'),
        ]);

        if ($response->failed()) {
            return redirect('/')->withErrors('Failed to authenticate with Webex');
        }

        $data = $response->json();
dd($data);
        // Store tokens securely
        session([
            'webex_access_token' => $data['access_token'],
            'webex_refresh_token' => $data['refresh_token'],
            'webex_token_expires' => now()->addSeconds($data['expires_in']),
        ]);

        return redirect()->route('dashboard');
    }

    public function makeCall(Request $request)
    {
        $accessToken = session('webex_access_token');

        if (!$accessToken) {
            return response()->json(['error' => 'Not authenticated with Webex'], 401);
        }

        $destination = $request->input('destination');

        // Placeholder response; actual implementation will use Webex JavaScript SDK
        return response()->json(['message' => 'Call initiated to ' . $destination]);
    }

}
