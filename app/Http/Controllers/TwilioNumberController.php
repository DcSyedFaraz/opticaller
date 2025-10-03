<?php

namespace App\Http\Controllers;

use App\Models\TwilioNumber;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TwilioNumberController extends Controller
{
    public function index()
    {
        $numbers = TwilioNumber::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get(['id', 'label', 'phone_number']);

        return Inertia::render('TwilioNumbers/Index', [
            'numbers' => $numbers,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|string|max:255',
            'phone_number' => ['required','regex:/^\+[1-9]\d{1,14}$/'],
        ], [
            'phone_number.regex' => 'Phone must be E.164 (e.g. +15551234567).',
        ]);

        TwilioNumber::create([
            'user_id' => auth()->id(),
            'label' => $data['label'],
            'phone_number' => $data['phone_number'],
        ]);

        return redirect()->route('twilio-numbers.index')
            ->with('message', 'Twilio number added.');
    }

    public function update(Request $request, TwilioNumber $twilio_number)
    {
        abort_unless($twilio_number->user_id === auth()->id(), 403);

        $data = $request->validate([
            'label' => 'required|string|max:255',
            'phone_number' => ['required','regex:/^\+[1-9]\d{1,14}$/'],
        ], [
            'phone_number.regex' => 'Phone must be E.164 (e.g. +15551234567).',
        ]);

        $twilio_number->update($data);

        return redirect()->route('twilio-numbers.index')
            ->with('message', 'Twilio number updated.');
    }

    public function destroy(TwilioNumber $twilio_number)
    {
        abort_unless($twilio_number->user_id === auth()->id(), 403);
        $twilio_number->delete();

        return redirect()->route('twilio-numbers.index')
            ->with('message', 'Twilio number deleted.');
    }
}
