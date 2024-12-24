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
            $transcriptions = $twilio->recordings($recordingSid)
                ->transcriptions
                ->read();
            Log::info('transcriptions Callback:', $transcriptions);
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
    public function adminToken(Request $request)
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
        $twilioAppSid = env('ADMIN_APP_SID');

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


    public function admincallback_data(Request $request)
    {

        Log::info("admincallback_data " . json_encode($request->all()));
        return response()->json(['status' => 'Transcription received']);
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
                    // 'record' => 'record-from-answer',
                    // 'recordingStatusCallback' => route('recording.callback'),
                    // 'recordingStatusCallbackMethod' => 'GET',
                    // 'recordingChannels' => 'dual',
                    'transcribeCallback' => route('transcription.callback'),

                ]);

                // $dial->number($to);
                $dial->conference($conferenceName, [
                    'beep' => 'false',
                    'startConferenceOnEnter' => true,
                    'endConferenceOnExit' => true,
                ]);

                // Initiate outbound call to add participant
                // Log::error('$dial occurred in call_data: ' . $response);
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
                    // 'timeout' => 20,
                    'transcribeCallback' => route('transcription.callback'),
                    'recordingStatusCallback' => route('recording.callback'),
                    'recordingStatusCallbackMethod' => 'POST',
                    'recordingChannels' => 'dual',
                    'statusCallback' => route('dial.callback') . '?conferenceName=' . urlencode($conferenceName),
                ]
            );


            Log::info("Outbound call initiated to {$participantNumber} with Call SID: " . $call);
        } catch (\Twilio\Exceptions\TwilioException $e) {
            Log::error("Failed to initiate outbound call to {$participantNumber}: " . $e->getMessage());
        }
    }
    public function joinAdminConference(Request $request)
    {
        Log::info("Full URL: " . $request->fullUrl());
        Log::info("conference data: " . json_encode($request->all()));
        $conferenceName = $request->input('conference_name') ?? $request->input('To');
        Log::info("cconferenceName: " . $conferenceName);

        if (!$conferenceName) {
            return response('Conference name is missing.', 400);
        }

        $response = new VoiceResponse();

        $dial = $response->dial('', [
            'callerId' => env('TWILIO_PHONE_NUMBER'),
            'record' => 'do-not-record',
        ]);
        $response->record([
            'transcribe' => true,
            'transcribeCallback' => route('transcription.callback'), // URL to handle transcription
            'action' => route('recording.callback'), // URL to handle recording status
            'method' => 'POST'
        ]);
        $dial->conference($conferenceName, [
            'beep' => false,
            'startConferenceOnEnter' => true,
            'endConferenceOnExit' => false,
        ]);

        return response($response)->header('Content-Type', 'text/xml');
        // $request->validate([
        //     'name' => 'required|string',
        //     'mute' => 'required|boolean',
        // ]);

        // $conferenceName = $request->input('name');
        // $mute = $request->input('mute', false);

        // $twimlUrl = route('conference.joinConference') . '?conference_name=' . urlencode($conferenceName) . '&mute=' . ($mute ? 'true' : 'false');

        // try {
        //     $twilioSid = env('TWILIO_ACCOUNT_SID');
        //     $twilioAuthToken = env('TWILIO_AUTH_TOKEN');
        //     $number = env('TWILIO_PHONE_NUMBER');

        //     $twilio = new Client($twilioSid, $twilioAuthToken);
        //     $call = $twilio->calls->create(
        //         env('ADMIN_PHONE_NUMBER'), // To
        //         $number, // From
        //         [
        //             'url' => $twimlUrl,
        //             'method' => 'POST',
        //             'record' => true,
        //             'transcribe' => 'true',
        //             // 'timeout' => 20,
        //             'transcribeCallback' => route('transcription.callback'),
        //             'recordingStatusCallback' => route('recording.callback'),
        //             'recordingStatusCallbackMethod' => 'POST',
        //             'recordingChannels' => 'dual',
        //             'statusCallback' => route('dial.callback') . '?conferenceName=' . urlencode($conferenceName),
        //         ]
        //     );

        //     Log::info("Outbound call initiated to " . env('ADMIN_PHONE_NUMBER') . " with Call SID: " . $call->sid);
        // } catch (\Twilio\Exceptions\TwilioException $e) {
        //     Log::error("Failed to initiate outbound call to " . env('ADMIN_PHONE_NUMBER') . ": " . $e->getMessage());
        // }

        // Log::debug("Admin joinConference called");
        // return response()->json(['message' => 'Admin added to conference']);
    }


    public function joinConference(Request $request)
    {
        Log::info("conference data: " . json_encode($request->all()));
        $conferenceName = $request->query('conference_name');

        if (!$conferenceName) {
            return response('Conference name is missing.', 400);
        }

        $response = new VoiceResponse();

        $dial = $response->dial('', [
            'callerId' => env('TWILIO_PHONE_NUMBER'),
            'record' => 'do-not-record',
        ]);
        $response->record([
            'transcribe' => true,
            'transcribeCallback' => route('transcription.callback'), // URL to handle transcription
            'action' => route('recording.callback'), // URL to handle recording status
            'method' => 'POST'
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

        $conferenceData = array_map(function ($conference) {
            return [
                'sid' => $conference->sid,
                'friendlyName' => $conference->friendlyName,
            ];
        }, $conferences);

        return inertia('Conferences/List', [
            'conferences' => $conferenceData,
        ]);
    }

    public function handleDialCallback(Request $request)
    {
        $dialCallStatus = $request->input('CallStatus'); // e.g., 'completed', 'no-answer', 'busy', etc.
        $conferenceName = $request->input('conferenceName'); // Retrieved from query parameter

        Log::info("Conference Name: {$conferenceName}");

        $response = new VoiceResponse();

        if (in_array($dialCallStatus, ['no-answer', 'busy', 'failed', 'canceled'])) {
            // End the conference by removing the agent from the conference
            $client = new Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));

            $participants = $client->conferences->read([
                'friendlyName' => $conferenceName
            ]);

            Log::info('Complete Participants: ' . json_encode($participants[0]->toArray()));

            try {
                // Fetch all participants in the conference
                $client->conferences($participants[0]->sid)
                    // ->participants($participants[0]->sid)
                    ->update(['status' => 'completed']);
                Log::info("Agent removed from conference {$conferenceName} due to dial status {$dialCallStatus}.");
                // foreach ($participants as $participant) {
                //     if ($participant->to === env('TWILIO_PHONE_NUMBER')) {
                //         // Remove the agent from the conference by updating their status to 'completed'

                //     }
                // }

            } catch (\Exception $e) {
                Log::error("Error ending conference {$conferenceName}: " . $e->getMessage());
            }
        }

        // Optionally, you can redirect or provide further instructions
        // For now, just respond with an empty response
        return response($response)->header('Content-Type', 'text/xml');
    }


}
