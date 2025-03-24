<?php

namespace App\Http\Controllers;

use App\Models\Transcription;
use Illuminate\Http\Request;
use Twilio\Rest\Client;

class TranscriptionController extends Controller
{
    public function getTranscription(string $recordingSid)
    {
        // Twilio credentials from .env
        $accountSid = env('TWILIO_ACCOUNT_SID');
        $authToken = env('TWILIO_AUTH_TOKEN');
        $serviceSid = 'GAc6ca0b4c1682139e7a25ebe8e13587d9';

        // Initialize Twilio client
        $twilio = new Client($accountSid, $authToken);

        try {
            // Create a new transcription for the recording
            $transcription = $twilio->intelligence->v2->transcripts->read([], 10);

            $sentences = $twilio->intelligence->v2
                ->transcripts($transcription[0]->sid)
                ->sentences->read([], 20);

            foreach ($sentences as $record) {
                print $record->transcript . PHP_EOL;
            }

            // return response()->json($transcription[0]->toArray());
            // Retrieve the transcription text
            $transcriptDetails = $twilio->intelligence->v2->transcripts('GT12a782ff731a0862a74aa06f27455d08')
                ->fetch();

            return response()->json([
                'status' => 'success',
                'transcription_text' => $transcriptDetails->transcriptionText,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function listActivetranscription(Request $request)
    {

        // Fetch active transcriptions
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $sortField = $request->input('sortField', 'created_at');
        $sortOrder = $request->input('sortOrder', 'desc');

        $transcriptions = Transcription::where('status', 'completed')->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page)
            ->appends($request->only(['per_page', 'sortField', 'sortOrder']));

        return inertia('Conferences/Transcription', [
            'transcriptions' => $transcriptions,
        ]);
    }
    public function destroy(Transcription $transcription)
    {
        $transcription->delete();
        return redirect()->back()->with('message', 'Transcription deleted successfully.');
    }
}
