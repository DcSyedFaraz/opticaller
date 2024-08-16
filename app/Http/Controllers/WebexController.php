<?php

namespace App\Http\Controllers;

use Http;
use Illuminate\Http\Request;

class WebexController extends Controller
{
    public function index()
    {
        return inertia('Users/new call');
    }
    public function authorizeWebex()
    {
        $query = http_build_query([
            'client_id' => config('services.webex.client_id'),
            'redirect_uri' => config('services.webex.redirect_uri'),
            'response_type' => 'code',
            'scope' => 'spark:all', // Adjust based on your needs
        ]);

        return redirect('https://webexapis.com/v1/authorize?' . $query);
    }

    public function callback(Request $request)
    {
        $response = Http::asForm()->post('https://webexapis.com/v1/access_token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.webex.client_id'),
            'client_secret' => config('services.webex.client_secret'),
            'redirect_uri' => config('services.webex.redirect_uri'),
            'code' => $request->code,
        ]);

        $accessToken = $response->json()['access_token'];

        // Store the access token for future requests
        session(['webex_access_token' => $accessToken]);

        return redirect('/webex/index')->with('success', 'Successfully authenticated with Webex!');
    }
    public function makeCall(Request $request)
    {
        $accessToken = session('webex_access_token');

        $response = Http::withToken($accessToken)->post('https://webexapis.com/v1/telephony/calls', [
            'destination' => $request->input('destination'),
        ]);

        return response()->json($response->json());
    }
    public function handleCandidate(Request $request)
    {
        $accessToken = session('webex_access_token');

        // Forward the ICE candidate to Webex or to the other peer
        $response = Http::withToken($accessToken)->post('https://webexapis.com/v1/telephony/candidates', [
            'candidate' => $request->input('candidate'),
            // Additional necessary data
        ]);

        return response()->json($response->json());
    }

}
