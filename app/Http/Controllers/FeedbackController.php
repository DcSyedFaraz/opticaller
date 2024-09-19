<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\SubProject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeedbackController extends Controller
{
    public function index()
    {
        $subProject = SubProject::with('feedbacks')->get();
        return Inertia::render('Projects/FeedbackManagement', [
            'subProjects' => $subProject,
        ]);
    }

    public function store($subProjectId, Request $request, )
    {
        // dd($request->all(),$subProjectId);
        $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ]);

        $subProject = SubProject::findOrFail($subProjectId);
        $subProject->feedbacks()->create($request->only(['label', 'value']));

        return redirect()->back()->with('success', 'Feedback added successfully.');
    }

    public function update(Request $request, $feedbackId)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ]);

        $feedback = Feedback::findOrFail($feedbackId);
        $feedback->update($request->only(['label', 'value']));

        return redirect()->back()->with('success', 'Feedback updated successfully.');
    }

    public function destroy($feedbackId)
    {
        $feedback = Feedback::findOrFail($feedbackId);
        $feedback->delete();

        return redirect()->back()->with('success', 'Feedback deleted successfully.');
    }
}
