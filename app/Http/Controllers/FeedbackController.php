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
        $feedbacks = Feedback::with('subProjects')->get();
        $subProjects = SubProject::all();
        return Inertia::render('Projects/FeedbackManagement', compact('feedbacks', 'subProjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'no_validation' => 'required|boolean',
            'sub_project_ids' => 'required|array',
            'sub_project_ids.*' => 'exists:sub_projects,id',
        ]);

        $feedback = Feedback::create([
            'label' => $request->label,
            'value' => $request->value,
            'no_validation' => $request->no_validation,
        ]);

        $feedback->subProjects()->attach($request->input('sub_project_ids'));

        return redirect()->back()->with('success', 'Feedback added successfully.');
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'no_validation' => 'required|boolean',
            'sub_project_ids' => 'required|array',
            'sub_project_ids.*' => 'exists:sub_projects,id',
        ]);

        $feedback = Feedback::findOrFail($id);
        $feedback->update([
            'label' => $request->label,
            'value' => $request->value,
            'no_validation' => $request->no_validation,
        ]);

        $feedback->subProjects()->sync($request->input('sub_project_ids'));

        return redirect()->back()->with('success', 'Feedback updated successfully.');
    }
    public function validation(Request $request, $id)
    {
        $request->validate([
            'no_validation' => 'required|boolean',
        ]);

        $feedback = Feedback::findOrFail($id);
        $feedback->update([
            'no_validation' => $request->no_validation,
        ]);
        return redirect()->route('feedbacks.index')->with('success', 'Feedback updated successfully.');
    }


    public function destroy($feedbackId)
    {
        $feedback = Feedback::findOrFail($feedbackId);
        $feedback->subProjects()->detach();
        $feedback->delete();

        return redirect()->back()->with('success', 'Feedback deleted successfully.');
    }
}
