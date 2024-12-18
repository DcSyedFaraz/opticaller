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
use Twilio\Rest\Client;
use Twilio\TwiML\VoiceResponse;

class CallController extends Controller
{
    public function index()
    {
        return Inertia::render(
            'Users/TwilioCallComponent'
        );
    }
    public function handleRecordingCallback(Request $request)
    {
        // Log recording details
        Log::info('Recording Callback:', $request->all());

        $recordingSid = $request->input('RecordingSid');
        $recordingUrl = $request->input('RecordingUrl'); // URL to access the recording (without file extension)
        $recordingDuration = $request->input('RecordingDuration');
        $callSid = $request->input('CallSid');

        // Initialize Twilio REST Client
        $twilioSid = env('TWILIO_ACCOUNT_SID');
        $twilioAuthToken = env('TWILIO_AUTH_TOKEN');
        $twilio = new Client($twilioSid, $twilioAuthToken);

        try {
            // Request transcription for the recording
            // $transcripts = $twilio->intelligence->v2->transcripts->read([]);


            // print $transcription->accountSid;
            // foreach ($transcripts as $record) {
            // print $record->accountSid;
            // $transcript = $twilio->intelligence->v2
            //     ->transcripts($record->sid)
            //     ->fetch();

            //     $transcriptText = $record->toArray();

            //     Log::info('Transcription requested:', [
            //         'TranscriptText' => $transcriptText
            //     ]);
            // }
        } catch (\Twilio\Exceptions\RestException $e) {
            Log::error('Error requesting transcription:', ['message' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::error('General error:', ['message' => $e->getMessage()]);
        }

        // Optionally, save recording details to your database
        // Recording::create([...]);

        return response()->json(['status' => 'Recording received']);
    }

    public function handleTranscriptionCallback(Request $request)
    {
        // Log transcription details
        Log::info('Transcription Callback:', $request->all());

        $transcriptionSid = $request->input('TranscriptionSid');
        $transcriptionText = $request->input('TranscriptionText');
        $recordingSid = $request->input('RecordingSid');

        // Optionally, save transcription details to your database
        // Transcription::create([
        //     'transcription_sid' => $transcriptionSid,
        //     'recording_sid' => $recordingSid,
        //     'transcription_text' => $transcriptionText,
        // ]);

        // You can also trigger other actions, such as notifying users or processing the text

        return response()->json(['status' => 'Transcription received']);
    }
    public function handleTranscriptionCallbacks(Request $request)
    {
        // Log transcription details
        Log::info('Transcription Callbackssssss:', $request->all());
        $twilioSid = env('TWILIO_ACCOUNT_SID');
        $twilioAuthToken = env('TWILIO_AUTH_TOKEN');
        $twilio = new Client($twilioSid, $twilioAuthToken);
        $transcript = $twilio->intelligence->v2
            ->transcripts($request->TranscriptionSid)
            ->fetch();

        // $transcriptText = $$request->toArray();

        Log::info('Transcription got:', [
            'TranscriptText' => $transcript->toArray()
        ]);

        return response()->json(['status' => 'Transcription received']);
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
        try {
            Log::info($request->all());

            $response = new VoiceResponse();

            $to = $request->input('To');
            $number = env('TWILIO_PHONE_NUMBER');

            if ($to) {

                $conferenceName = 'SupportConference_' . uniqid();
                $dial = $response->dial('', [
                    'callerId' => $number,
                    'transcribe' => "true",
                    'record' => 'record-from-answer',
                    'recordingStatusCallback' => route('recording.callback'),
                    'recordingStatusCallbackMethod' => 'GET',
                    'recordingChannels' => 'dual',
                    'transcribeCallback' => route('transcription.callback'),

                ]);

                // $dial->number($to);
                $dial->conference($conferenceName, [
                    'beep' => 'false',
                    'startConferenceOnEnter' => true,
                    'endConferenceOnExit' => true,
                ]);

                // Initiate outbound call to add participant
                $this->addParticipantToConference($conferenceName, $to);
            } else {
                // Incoming call
                $response->say('Thank you for calling!');
            }

            // Return the TwiML response as XML
            return response($response)->header('Content-Type', 'text/xml');
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Error occurred in call_data: ' . $e->getMessage());

            // Optionally, return a failure TwiML response
            $response = new VoiceResponse();
            $response->say('An error occurred while processing your request. Please try again later.');

            // Return the error response as XML
            return response($response)->header('Content-Type', 'text/xml');
        }
    }
    protected function addParticipantToConference($conferenceName, $participantNumber)
    {
        $twimlUrl = route('conference.joinConference') . '?conference_name=' . urlencode($conferenceName);

        try {
            $twilioSid = env('TWILIO_ACCOUNT_SID');
            $twilioAuthToken = env('TWILIO_AUTH_TOKEN');
            $number = env('TWILIO_PHONE_NUMBER');

            $twilio = new Client($twilioSid, $twilioAuthToken);
            $call = $twilio->calls->create(
                $participantNumber, // To
                $number, // From
                [
                    'url' => $twimlUrl,
                    'method' => 'POST',
                    'record' => true,
                    'transcribe' => 'true',
                    'transcribeCallback' => route('transcription.callback'),
                    'recordingStatusCallback' => route('recording.callback'),
                    'recordingStatusCallbackMethod' => 'POST',
                    'recordingChannels' => 'dual',
                    'statusCallback' => route('dial.callback') . '?conferenceName=' . urlencode($conferenceName),
                ]
            );

            Log::info("Outbound call initiated to {$participantNumber} with Call SID: {$call->sid}");
        } catch (\Twilio\Exceptions\TwilioException $e) {
            Log::error("Failed to initiate outbound call to {$participantNumber}: " . $e->getMessage());
        }
    }
    public function joinConference(Request $request)
    {
        Log::info($request->all());
        $conferenceName = $request->query('conference_name');

        if (!$conferenceName) {
            return response('Conference name is missing.', 400);
        }

        $response = new VoiceResponse();

        $dial = $response->dial('', [
            'callerId' => env('TWILIO_PHONE_NUMBER'),
            'record' => 'do-not-record',
        ]);

        $dial->conference($conferenceName, [
            'beep' => false,
            'startConferenceOnEnter' => true,
            'endConferenceOnExit' => true,
        ]);

        return response($response)->header('Content-Type', 'text/xml');
    }
    public function listActiveConferences()
    {
        $client = new Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));

        // Fetch active conferences
        $conferences = $client->conferences->read([
            'status' => 'in-progress'
        ], 5);
        // dd($conferences);
        // $conferences = $client->conferences->read([
        //     'status' => 'in-progress'
        // ]);

        // Transform the data as needed
        // $conferenceList = collect($conferences)->map(function ($conf) {
        //     return [
        //         'sid' => $conf->sid,
        //         'friendlyName' => $conf->friendlyName,
        //         'dateCreated' => $conf->dateCreated,
        //         'participantCount' => $conf->participantCount,
        //     ];
        // });

        return response()->json([
            'conferences' => $conferences[0]->toArray,
        ]);
    }
    public function handleDialCallback(Request $request)
    {
        $dialCallStatus = $request->input('DialCallStatus'); // e.g., 'completed', 'no-answer', 'busy', etc.
        $conferenceName = $request->input('conferenceName'); // Retrieved from query parameter

        Log::info("Dial Call Status: {$dialCallStatus}");
        Log::info("Conference Name: {$conferenceName}");

        $response = new VoiceResponse();

        if (in_array($dialCallStatus, ['no-answer', 'busy', 'failed', 'canceled'])) {
            // End the conference by removing the agent from the conference
            $client = new Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));

            try {
                // Fetch all participants in the conference
                $participants = $client->conferences($conferenceName)->participants->read();

                foreach ($participants as $participant) {
                    if ($participant->to === env('TWILIO_PHONE_NUMBER')) {
                        // Remove the agent from the conference by updating their status to 'completed'
                        $client->conferences($conferenceName)
                            ->participants($participant->sid)
                            ->update(['status' => 'completed']);

                        Log::info("Agent removed from conference {$conferenceName} due to dial status {$dialCallStatus}.");
                    }
                }

            } catch (\Exception $e) {
                Log::error("Error ending conference {$conferenceName}: " . $e->getMessage());
            }
        }

        // Optionally, you can redirect or provide further instructions
        // For now, just respond with an empty response
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
