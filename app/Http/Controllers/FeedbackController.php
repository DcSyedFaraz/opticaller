<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\SubProject;
use DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = Feedback::with('subProjects')->orderBy('order')->get();
        $subProjects = SubProject::all();
        return Inertia::render('Projects/FeedbackManagement', compact('feedbacks', 'subProjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'no_validation' => 'required|boolean',
            'no_statistics' => 'required|boolean',
            'sub_project_ids' => 'required|array',
            'sub_project_ids.*' => 'exists:sub_projects,id',
        ]);

        $feedback = Feedback::create([
            'label' => $request->label,
            'value' => $request->value,
            'no_validation' => $request->no_validation,
            'no_statistics' => $request->no_statistics,
        ]);

        $feedback->subProjects()->attach($request->input('sub_project_ids'));

        return redirect()->back()->with('success', 'Feedback added successfully.');
    }

    public function reorder(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'orderedIds' => 'required|array',
            'orderedIds.*' => 'integer|exists:feedback,id',
        ]);

        $orderedIds = $request->orderedIds;

        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                Feedback::where('id', $id)->update(['order' => $index + 1]);
            }
        });

        return back()->with('message', 'Feedbacks reordered successfully.');
    }
    public function update(Request $request, $id)
    {
        $feedback = Feedback::findOrFail($id);
        $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'no_validation' => 'required|boolean',
            'no_statistics' => 'required|boolean', // Add this line
            'sub_project_ids' => 'required|array',
            'sub_project_ids.*' => 'exists:sub_projects,id',
        ]);

        $feedback->update([
            'label' => $request->label,
            'value' => $request->value,
            'no_validation' => $request->no_validation,
            'no_statistics' => $request->no_statistics, // Add this line
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
