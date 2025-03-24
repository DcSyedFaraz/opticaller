<?php

namespace App\Http\Controllers;

use App\Events\CallEnded;
use App\Events\CallInitiated;
use App\Events\IncomingCall;
use App\Models\Transcription;
use DB;
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
        Log::channel('call')->info('Recording Callback:', $request->all());

        $recordingSid = $request->input('RecordingSid');
        $recordingUrl = $request->input('RecordingUrl'); // URL to access the recording (without file extension)
        $recordingDuration = $request->input('RecordingDuration');
        $callSid = $request->input('CallSid');

        // Initialize Twilio REST Client
        $twilioSid = env('TWILIO_ACCOUNT_SID');
        $twilioAuthToken = env('TWILIO_AUTH_TOKEN');
        $twilio = new Client($twilioSid, $twilioAuthToken);

        try {
            // Extract recording details
            $recordingSid = $request->input('RecordingSid');
            $recordingUrl = $request->input('RecordingUrl');
            $callSid = $request->input('CallSid');
            $callerIdentity = $request->input('callerIdentity');
            $addressID = $request->input('addressID');
            $toNumber = $request->input('toNumber');

            // Initialize Twilio client
            $twilio = new Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));

            try {
                $serviceSid = env('TWILIO_VOICE_INTELLIGENCE_SERVICE_SID');

                // $transcription = $twilio
                //     ->recordings($recordingSid)
                //     ->transcriptions
                //     ->create([
                //         'transcriptionServiceSid' => $serviceSid,
                //     ]);

                // Save a pending record in the database
                Transcription::insert([
                    'recording_sid' => $recordingSid,
                    'callerIdentity' => $callerIdentity,
                    'address_id' => $addressID,
                    'to_number' => $toNumber,
                    'call_sid' => $callSid,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                // Transcription::updateOrInsert(
                //     ['call_sid' => $callSid], // Matching condition
                //     [
                //         'recording_sid' => $recordingSid,
                //         'callerIdentity' => $callerIdentity,
                //         'status' => 'pending',
                //         'created_at' => now(),
                //         'updated_at' => now()
                //     ]
                // );

                // Delete the recording (assuming Twilio fetches audio immediately)
                $twilio->recordings($recordingSid)->delete();
                Log::channel('call')->info("Recording with SID {$recordingSid} has been deleted successfully.");

            } catch (\Twilio\Exceptions\RestException $e) {
                Log::channel('call')->error('Error requesting transcription:', ['message' => $e->getMessage()]);
            }
        } catch (\Exception $e) {
            Log::channel('call')->error('General error:', ['message' => $e->getMessage()]);
        }


        // Optionally, save recording details to your database
        // Recording::create([...]);

        return response('OK', 200);

    }

    public function handleTranscriptionCallback(Request $request)
    {
        // Log transcription details
        Log::channel('call')->info('Transcription Callback:', $request->all()); {
            // Log the incoming callback for debugging

            // Extract transcript details from the callback
            $transcriptSid = $request->input('transcript_sid');
            Log::channel('call')->info("Transcription SID: $transcriptSid");


            try {
                // Initialize Twilio client
                $twilio = new Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));

                $transcript = $twilio->intelligence->v2
                    ->transcripts($transcriptSid)
                    ->fetch();
                Log::channel('call')->info('Transcript:', $transcript->toArray());

                $sourceId = data_get($transcript->toArray(), 'channel.media_properties.source_sid');
                Log::channel('call')->info('Transcript source_sid: ' . $sourceId);

                // Fetch all sentences for the transcript
                $sentences = $twilio->intelligence->v2->transcripts($transcriptSid)->sentences->read();
                // Log::channel('call')->info('Sentences:', $sentences);
                // Construct the full transcription text
                $fullText = '';
                foreach ($sentences as $sentence) {
                    $speaker = $sentence->mediaChannel == 1 ? 'Agent' : 'Caller';
                    // $channel = $sentence->mediaChannel ?? 'Unknown';
                    $text = $sentence->transcript ?? '';
                    // $fullText .= "[Channel {$channel}]: {$text}\n";
                    $fullText .= "[{$speaker}]: {$text}\n";
                }
                Log::channel('call')->info('Full Text: ' . $fullText);
                // Save to database
                Transcription::updateOrInsert(
                    ['recording_sid' => $sourceId], // Matching condition
                    [
                        'transcription_sid' => $transcriptSid,
                        'transcription_text' => $fullText,
                        'status' => 'completed',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );


                Log::channel('call')->info("Transcript {$transcriptSid} completed and saved.");

            } catch (\Exception $e) {
                Log::channel('call')->error("Error processing transcript {$transcriptSid}: {$e->getMessage()}");
                // Optionally mark as 'error' or implement retry logic
                return response()->json(['status' => 'error'], 500);
            }

            return response()->json(['status' => 'ok']);
        }
    }
    public function handleTranscriptionCallbacks(Request $request)
    {
        // Log the incoming callback for debugging
        Log::channel('call')->info('Transcript Callback:', $request->all());

        // Extract transcript details from the callback
        $transcriptSid = $request->input('Sid');
        $status = $request->input('Status');

        // Check if the transcript exists in the database
        // $transcriptRecord = DB::table('newtranscripts')
        //     ->where('transcription_sid', $transcriptSid)
        //     ->firstOrCreate();

        // if (!$transcriptRecord) {
        //     Log::channel('call')->warning("Transcript {$transcriptSid} not found in database.");
        //     return response()->json(['status' => 'error'], 404);
        // }

        // // Skip if already processed
        // if ($transcriptRecord->status !== 'pending') {
        //     Log::channel('call')->info("Transcript {$transcriptSid} already processed with status: {$transcriptRecord->status}");
        //     return response()->json(['status' => 'already_processed']);
        // }

        try {
            if ($status === 'completed') {
                // Initialize Twilio client
                $twilio = new Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));

                // Fetch all sentences for the transcript
                $sentences = $twilio->intelligence->v2->transcripts($transcriptSid)->sentences->read();

                // Construct the full transcription text
                $fullText = '';
                foreach ($sentences as $sentence) {
                    $channel = $sentence->mediaChannel ?? 'Unknown';
                    $text = $sentence->transcript ?? '';
                    $fullText .= "[Channel {$channel}]: {$text}\n";
                }

                // Save to database
                Transcription::where('transcription_sid', $transcriptSid)
                    ->updateOrInsert([
                        'transcription_text' => $fullText,
                        'status' => 'completed',
                        'updated_at' => now()
                    ]);

                Log::channel('call')->info("Transcript {$transcriptSid} completed and saved.");
            } elseif ($status === 'failed') {
                // Handle transcription failure
                Transcription::where('transcription_sid', $transcriptSid)
                    ->update([
                        'status' => 'failed',
                        'updated_at' => now()
                    ]);
                Log::channel('call')->error("Transcript {$transcriptSid} failed.");
            } else {
                Log::channel('call')->info("Transcript {$transcriptSid} received status: {$status}. No action taken.");
            }
        } catch (\Exception $e) {
            Log::channel('call')->error("Error processing transcript {$transcriptSid}: {$e->getMessage()}");
            // Optionally mark as 'error' or implement retry logic
            return response()->json(['status' => 'error'], 500);
        }

        return response()->json(['status' => 'ok']);
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

        Log::channel('call')->info("admincallback_data " . json_encode($request->all()));
        return response()->json(['status' => 'Transcription received']);
    }
    public function updateStatus(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'status' => 'required|string|in:completed',
            'conferenceSid' => 'required_without:conferenceName|string',
            'conferenceName' => 'required_without:conferenceSid|string',
        ]);

        $status = $request->input('status');
        $conferenceSid = $request->input('conferenceSid');
        $conferenceName = $request->input('conferenceName');

        // Initialize Twilio Client
        $twilio = new Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));

        try {
            // Determine the conference SID
            if ($conferenceSid) {
                // Use conference SID directly
                $conference = $twilio->conferences($conferenceSid)->fetch();
            } else {
                // Fetch conference by name
                $conferences = $twilio->conferences
                    ->read(["friendlyName" => $conferenceName], 1);

                if (empty($conferences)) {
                    return response()->json(['error' => 'Conference not found.'], 404);
                }

                $conference = $conferences[0];
            }
            Log::channel('call')->alert("message: $conference");
            // Update the conference status to 'completed'
            $twilio->conferences($conference->sid)
                ->update(['status' => $status]);

            Log::channel('call')->info("Conference {$conference->sid} status updated to {$status}.");

            return response()->json(['message' => 'Conference status updated successfully.'], 200);
        } catch (\Twilio\Exceptions\RestException $e) {
            Log::channel('call')->error("Twilio REST Exception: " . $e->getMessage());
            return response()->json(['error' => 'Failed to update conference status.'], 500);
        } catch (\Exception $e) {
            Log::channel('call')->error("General Exception: " . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }
    public function call_data(Request $request)
    {
        try {
            Log::channel('call')->info($request->all());

            $response = new VoiceResponse();

            $to = $request->input('To');
            $number = env('TWILIO_PHONE_NUMBER');

            if ($to) {

                // $conferenceName = 'SupportConference_' . uniqid();
                $conferenceName = $request->Caller;
                $addressID = $request->addressID;
                $statusCallbackUrl = route('recording.callback') . '?callerIdentity=' . urlencode($conferenceName);
                $dial = $response->dial('', [
                    'callerId' => $number,
                    'transcribe' => "true",
                    // 'transcribeCallback' => route('transcription.callback'),
                    // 'statusCallbackEvent' => ['initiated', 'ringing', 'answered', 'completed'],
                    // 'record' => 'record-from-ringing-dual',
                    // 'recordingStatusCallback' => $statusCallbackUrl,
                    // 'recordingStatusCallbackMethod' => 'GET'
                ]);

                // $dial->number($to);
                $dial->conference($conferenceName, [
                    'statusCallbackMethod' => 'GET',
                    'statusCallback' => route('dial.callback') . '?conferenceName=' . urlencode($conferenceName),
                    'beep' => false,
                    'startConferenceOnEnter' => true,
                    'endConferenceOnExit' => true,
                    'waitUrl' => 'http://com.twilio.music.electronica.s3.amazonaws.com/Kaer_Trouz_-_Seawall_Stepper.mp3',
                    'waitMethod' => 'GET',

                ]);

                $this->addParticipantToConference($conferenceName, $to, $addressID);
            } else {
                // Incoming call
                $response->say('Thank you for calling!');
            }

            // Return the TwiML response as XML
            return response($response)->header('Content-Type', 'text/xml');
        } catch (\Exception $e) {
            // Log the exception
            Log::channel('call')->error('Error occurred in call_data: ' . $e->getMessage());

            // Optionally, return a failure TwiML response
            $response = new VoiceResponse();
            $response->say('An error occurred while processing your request. Please try again later.');

            // Return the error response as XML
            return response($response)->header('Content-Type', 'text/xml');
        }
    }
    protected function addParticipantToConference($conferenceName, $participantNumber, $addressID)
    {
        $twimlUrl = route('conference.joinConference') . '?conference_name=' . urlencode($conferenceName);

        try {
            $twilioSid = env('TWILIO_ACCOUNT_SID');
            $twilioAuthToken = env('TWILIO_AUTH_TOKEN');

            $twilio = new Client($twilioSid, $twilioAuthToken);
            $participant = $twilio->conferences($conferenceName)
                ->participants
                ->create(
                    env('TWILIO_PHONE_NUMBER'),
                    $participantNumber,
                    [
                        // 'muted' => true,
                        'beep' => false,
                        'startConferenceOnEnter' => true,
                        'endConferenceOnExit' => true,
                        'timeout' => 20,
                        'statusCallback' => route('dial.callback') . '?conferenceName=' . urlencode($conferenceName), // Optional: URL to receive status updates
                        'statusCallbackEvent' => ['initiated', 'ringing', 'answered', 'completed'],
                        'record' => 'true', // Start recording after answer
                        'recordingStatusCallback' => route('recording.callback') . '?callerIdentity=' . urlencode($conferenceName) . '&addressID=' . urlencode($addressID) . '&toNumber=' . urlencode($participantNumber),
                        'recordingStatusCallbackMethod' => 'GET'
                    ]
                );
            // $callSid = $participant->callSid;

            // $call = $twilio->calls($callSid)->fetch();

            // // Update the call to start recording
            // $call->update([
            //     'record' => 'record-from-answer-dual', // Start recording after answer
            //     'recordingStatusCallback' => route('recording.callback') . '?callerIdentity=' . urlencode($conferenceName),
            //     'recordingStatusCallbackMethod' => 'GET'
            // ]);

            // Transcription::create([
            //     'call_sid' => $callSid,
            //     'to_number' => $participantNumber,
            // ]);

            Log::channel('call')->info("Outbound call initiated to {$participantNumber} with Call SID: " . $participant);
        } catch (\Twilio\Exceptions\TwilioException $e) {
            Log::channel('call')->error("Failed to initiate outbound call to {$participantNumber}: " . $e->getMessage());
        }
    }

    public function joinConference(Request $request)
    {
        Log::channel('call')->info("conference data: " . json_encode($request->all()));
        $conferenceName = $request->query('conference_name');

        if (!$conferenceName) {
            return response('Conference name is missing.', 400);
        }

        $response = new VoiceResponse();

        $dial = $response->dial('', [
            'callerId' => env('TWILIO_PHONE_NUMBER'),
            'record' => 'true',
        ]);
        $response->record([
            'transcribe' => true,
            // 'transcribeCallback' => route('transcription.callback'), // URL to handle transcription
            // 'recordingStatusCallback' => route('recording.callback'), // URL to handle recording status
            'recordingStatusCallbackMethod' => 'POST'
        ]);
        $dial->conference($conferenceName, [
            'beep' => false,
            'startConferenceOnEnter' => false,
            'endConferenceOnExit' => true,
        ]);

        return response($response)->header('Content-Type', 'text/xml');
    }
    public function statusCallback(Request $request)
    {
        // Retrieve necessary parameters from the callback
        $conferenceSid = $request->input('ConferenceSid');
        $participantSid = $request->input('ParticipantSid');
        $event = $request->input('StatusCallbackEvent'); // 'start' or 'end'
        Log::channel('call')->info('statusCallback: ' . $request->all());
        // Only handle the 'start' event to mute participants upon joining
        if ($event === 'start') {
            $this->muteParticipant($conferenceSid, $participantSid);
        }

        return response('OK', 200);
    }

    /**
     * Mute a participant in the conference
     */
    protected function muteParticipant($conferenceSid, $participantSid)
    {
        // Initialize Twilio REST Client
        // $twilio = new Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));

        // try {
        //     // Update the participant's muted status to true
        //     $twilio->conferences($conferenceSid)
        //         ->participants($participantSid)
        //         ->update(['muted' => true]);

        //     \Log::channel('call')->info("Participant {$participantSid} in Conference {$conferenceSid} has been muted.");
        // } catch (\Exception $e) {
        //     \Log::channel('call')->error("Failed to mute participant {$participantSid}: " . $e->getMessage());
        // }
    }
    public function joinAdminConference(Request $request)
    {
        Log::channel('call')->info("Full URL: " . $request->fullUrl());
        Log::channel('call')->info("conference data: " . json_encode($request->all()));
        $conferenceName = $request->input('conference_name') ?? $request->input('To');
        Log::channel('call')->info("cconferenceName: " . $conferenceName);

        if (!$conferenceName) {
            return response('Conference name is missing.', 400);
        }

        $response = new VoiceResponse();

        $dial = $response->dial('', [
            'callerId' => env('TWILIO_PHONE_NUMBER'),
        ]);

        $dial->conference($conferenceName, [
            'beep' => false,
            'muted' => true,
            'startConferenceOnEnter' => false,
            'endConferenceOnExit' => false,
        ]);

        return response($response)->header('Content-Type', 'text/xml');

    }


    public function listActiveConferences()
    {
        $client = new Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));

        // Fetch active conferences
        $conferences = $client->conferences->read([
            'status' => 'in-progress'
        ], 50);
        // Log::channel('call')->info('conferences: ' . $conferences[0]);
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

        // Log::channel('call')->info("Dial CallBack Conference Name: {$conferenceName}, {$dialCallStatus}");

        $response = new VoiceResponse();

        if (in_array($dialCallStatus, ['no-answer', 'busy', 'failed', 'canceled', 'completed'])) {
            // End the conference by removing the agent from the conference
            $client = new Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));

            $participants = $client->conferences->read([
                'friendlyName' => $conferenceName
            ]);
            // for
            // Log::channel('call')->info('Complete Participants: ' . json_encode($participants));

            if (!empty($participants) && isset($participants[0])) {
                try {
                    // Fetch the first participant's SID
                    $conferences = $client->conferences->read([
                        'friendlyName' => $conferenceName,
                        'status' => 'in-progress'
                    ]);

                    if (!empty($conferences)) {
                        Log::channel('call')->info("Conference ." . json_encode($conferences));
                        $conference = $conferences[0];
                        $conferenceSid = $conference->sid;

                        // Optionally, mark the conference as completed to ensure it's terminated
                        $client->conferences($conferenceSid)->update(['status' => 'completed']);
                        Log::channel('call')->info("Conference {$conferenceSid} marked as completed.");
                    } else {
                        Log::channel('call')->warning("No active conference found with name {$conferenceName}.");
                    }
                } catch (\Exception $e) {
                    Log::channel('call')->error("Failed to update conference status: " . $e->getMessage());
                }
            } else {
                Log::channel('call')->warning("No participants found in the conference. Cannot complete conference {$conferenceName}.");
            }

        }

        // Optionally, you can redirect or provide further instructions
        // For now, just respond with an empty response
        return response($response)->header('Content-Type', 'text/xml');
    }
    public function callbackUser(Request $request)
    {
        $dialCallStatus = $request->input('CallStatus'); // e.g., 'completed', 'no-answer', 'busy', etc.
        $conferenceName = $request->input('From'); // Retrieved from query parameter

        Log::channel('call')->info("User Dial Callback - Conference Name: {$conferenceName}, Status: {$dialCallStatus}");

        // Define statuses that should trigger the conference termination
        $endStatuses = ['no-answer', 'busy', 'failed', 'canceled', 'completed'];

        // if (in_array($dialCallStatus, $endStatuses)) {
        //     $client = new Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));

        //     try {
        //         // Fetch the conference by friendly name and in-progress status
        //         $conferences = $client->conferences->read([
        //             'friendlyName' => $conferenceName,
        //             'status' => 'in-progress'
        //         ]);

        //         if (!empty($conferences)) {
        //             $conference = $conferences[0];
        //             $conferenceSid = $conference->sid;

        //             // Fetch all participants in the conference
        //             $participants = $client->conferences($conferenceSid)->participants->read();

        //             foreach ($participants as $participant) {
        //                 $participantSid = $participant->sid;
        //                 // Remove each participant by updating their status to 'completed'
        //                 $client->conferences($conferenceSid)
        //                     ->participants($participantSid)
        //                     ->update(['status' => 'completed']);
        //                 Log::channel('call')->info("Participant {$participantSid} removed from conference {$conferenceSid}.");
        //             }

        //             // Optionally, mark the conference as completed to ensure it's terminated
        //             $client->conferences($conferenceSid)->update(['status' => 'completed']);
        //             Log::channel('call')->info("Conference {$conferenceSid} marked as completed.");
        //         } else {
        //             // Log::channel('call')->warning("No active conference found with name {$conferenceName}.");
        //         }
        //     } catch (\Exception $e) {
        //         Log::channel('call')->error("Error handling dial callback: " . $e->getMessage());
        //     }
        // }

        // Respond with empty TwiML
        $response = new VoiceResponse();
        return response($response)->header('Content-Type', 'text/xml');
    }


}
