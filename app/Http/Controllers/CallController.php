<?php

namespace App\Http\Controllers;

use App\Events\CallEnded;
use App\Events\CallInitiated;
use App\Events\IncomingCall;
use Http;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Log;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VoiceGrant;
use Twilio\TwiML\VoiceResponse;

class CallController extends Controller
{
    public function index()
    {
        return Inertia::render(
            'Users/TwilioCallComponent'
        );
    }
    public function getToken(Request $request)
    {
        // dd($request->all());
        // Validate the request (optional)
        $request->validate([
            'identity' => 'required|string',
        ]);

        $identity = $request->input('identity');

        // Retrieve Twilio credentials from .env
        $twilioAccountSid = env('TWILIO_ACCOUNT_SID');
        $twilioApiKey = env('TWILIO_API_KEY');
        $twilioApiSecret = env('TWILIO_API_SECRET');
        $twilioAppSid = env('TWILIO_APP_SID');

        // Create access token
        $token = new AccessToken(
            $twilioAccountSid,
            $twilioApiKey,
            $twilioApiSecret,
            32400, // Token validity in seconds (e.g., 1 hour)
            $identity
        );

        // Create Voice grant
        $voiceGrant = new VoiceGrant();
        $voiceGrant->setOutgoingApplicationSid($twilioAppSid);
        // Optional: Restrict access to specific TwiML applications
        // $voiceGrant->setIncomingAllow(true);

        // Add grant to token
        $token->addGrant($voiceGrant);

        // Return token as JSON
        return response()->json([
            'token' => $token->toJWT(),
        ]);
    }


    public function call_data(Request $request)
    {
        Log::info($request->all());

        // Placeholder response; actual implementation will use Webex JavaScript SDK
        $response = new VoiceResponse();

        // Get the 'To' parameter from the request
        $to = $request->input('To');
        $number = env('TWILIO_PHONE_NUMBER');
        // dd($number);
        if ($to) {
            // Outgoing call
            $dial = $response->dial('', ['callerId' => $number]);
            $dial->number($to);
        } else {
            // Incoming call
            $response->say('Thank you for calling!');
        }

        // Return the TwiML response as XML
        return response($response)->header('Content-Type', 'text/xml');
    }

    // new code
    protected $baseUrl = 'https://my.cloudtalk.io/api/';

    // CloudTalk API credentials
    protected $apiKey;
    protected $apiSecret;

    public function __construct()
    {
        $this->apiKey = env('CLOUDTALK_API_KEY'); // Fetch from .env file
        $this->apiSecret = env('CLOUDTALK_API_SECRET'); // Fetch from .env file
    }
    public function initiateCall(Request $request)
    {
        $request->validate([
            'phoneNumber' => 'required|string',
        ]);

        $phoneNumber = $request->phoneNumber;

        try {
            $response = Http::withBasicAuth(env('CLOUDTALK_API_KEY'), env('CLOUDTALK_API_SECRET'))
                ->get('https://my.cloudtalk.io/api/calls/index.json', [
                    'phone_number' => $phoneNumber,
                ]);
            // dd($response);
            if ($response->successful()) {
                // Log::alert($response->json());
                $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                    ->get("{$this->baseUrl}agents/index.json");

                if ($response->successful()) {
                    Log::alert('Agents Retrieved: ' . json_encode($response->json()));
                    $agents = $response->json()['responseData']['data'];
                    if ($agents) {
                        $firstAgent = $agents[0]['Agent']; // Example: use the first agent
                        Log::alert('Agents Retrieved data: ' . json_encode($firstAgent));
                        // $phoneNumber = $phoneNumber; // The phone number to call

                        // return response()->json(['message' => $response->json()], 200);
                        // Make the call using the first agent's ID
                       $new = $this->makeCall($phoneNumber, $firstAgent['id']);
                    //    dd($new->json());
                    //    Log::alert("new: $new");
                    }
                }
                // Broadcast the call initiation
                broadcast(new CallInitiated($phoneNumber));

                return response()->json(['message' => 'Call initiated successfully.'], 200);
            } else {
                return response()->json(['error' => 'Failed to initiate call.'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error initiating call: ' . $e], 500);
        }
    }
    public function makeCall($phoneNumber, $agentId)
    {
        $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
        ->post("{$this->baseUrl}calls/create.json", [
                'callee_number' => $phoneNumber,
                'agent_id' => $agentId,
            ]);
            // dd( $phoneNumber,$agentId);
            if ($response->successful()) {
            Log::alert('Call Initiated: ' . json_encode($response->json()));
            return $response->json();
        }

        Log::error('Error initiating call: ' . $response->body());
        return response()->json(['error' => 'Failed to initiate call.'], $response->status());
        // return null;
    }
    /**
     * Hang up a call via CloudTalk API.
     * Note: You may need to pass additional identifiers to hang up a specific call.
     */
    public function hangUpCall(Request $request)
    {
        $request->validate([
            'callId' => 'required|string',
        ]);

        $callId = $request->callId;

        try {
            $response = Http::withBasicAuth(env('CLOUDTALK_API_KEY'), env('CLOUDTALK_API_SECRET'))
                ->post("https://my.cloudtalk.io/api/v3/calls/{$callId}/hangup");

            if ($response->successful()) {
                // Broadcast the call termination
                broadcast(new CallEnded($callId));

                return response()->json(['message' => 'Call ended successfully.'], 200);
            } else {
                return response()->json(['error' => 'Failed to end call.'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error ending call: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle incoming call webhook from CloudTalk.
     */
    public function handleWebhook(Request $request)
    {
        // Verify webhook signature if CloudTalk provides one for security

        $event = $request->input('event');
        $data = $request->input('data');

        switch ($event) {
            case 'incoming_call':
                $fromNumber = $data['from'];
                broadcast(new IncomingCall($fromNumber));
                break;

            // Handle other events as needed
            default:
                // Handle other events or ignore
                break;
        }

        return response()->json(['message' => 'Webhook handled.'], 200);
    }
}
