<?php

namespace App\Http\Controllers;

use Exception;
use Http;
use Illuminate\Http\Request;
use Twilio\Exceptions\RestException;
use Twilio\Rest\Client;

class WebexController extends Controller
{
    public $account_sid;
    public $auth_token;
    public $from;
    public $client;
    public function __construct()
    {
        // Twilio credentials
        // $this->account_sid = 'ACb2a01284e5e5466f344f8c13fa90e550';
        // $this->auth_token = '2f0b5f1564ba3c8c8076f54a3fb10608';
        $this->account_sid = env('TWILIO_ACCOUNT_SID');
        $this->auth_token = env('TWILIO_AUTH_TOKEN');

        //The twilio number you purchased
        $this->from = env('TWILIO_PHONE_NUMBER');
        // dd($this->account_sid, $this->auth_token);
        // Initialize the Programmable Voice API
        // dd(new Client($this->account_sid, $this->auth_token));
        $this->client = new Client($this->account_sid, $this->auth_token);
    }
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

        $this->validate($request, [
            'phone_number' => 'required|string',
        ]);

        try {
            // Lookup the phone number to ensure it's valid
//             $phone_number = $this->client->lookups->v1->phoneNumbers($request->phone_number)->fetch();
// dd($phone_number);
            // If the phone number is valid
            // if ($phone_number) {
                // Initiate the call and record it
                dd($request->phone_number);
                $call = $this->client->calls->create(
                    $request->phone_number, // Destination phone number
                    $this->from, // Valid Twilio phone number
                    [
                        // "record" => true,
                        "url" => "http://demo.twilio.com/docs/voice.xml"
                    ]
                );

                // Check if the call was successfully initiated
                if ($call) {
                    return response()->json(['message' => 'Call initiated successfully']);
                } else {
                    return response()->json(['message' => 'Call initiation failed'], 500);
                }
            // }
        } catch (RestException $e) {
            return response()->json(['error' => 'Twilio API error: ' . $e->getMessage()], 500);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }


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
