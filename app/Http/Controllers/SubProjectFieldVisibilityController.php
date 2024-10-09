<?php

namespace App\Http\Controllers;

use App\Models\SubProject;
use App\Models\SubProjectFieldVisibility;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Validator;

class SubProjectFieldVisibilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subProjects = SubProject::with('fieldVisibilities')->get();

        return Inertia::render('Feedback/FieldVisibilityManagement', [
            'subProjects' => $subProjects,

        ]);
    }

    /**
     * Store a newly created field visibility in storage.
     */
    public function update(Request $request)
    {
        dd($request->all());
        $request->validate([
            'sub_project_id' => 'required|exists:sub_projects,id',
            'fieldVisibilities' => 'required|array',
            'fieldVisibilities.*' => 'boolean',
        ]);

        foreach ($request->fieldVisibilities as $field_name => $is_hidden) {
            SubProjectFieldVisibility::updateOrCreate(
                [
                    'sub_project_id' => $request->sub_project_id,
                    'field_name' => $field_name,
                ],
                [
                    'is_hidden' => $is_hidden,
                ]
            );
        }

        return redirect()->back()->with('message', 'Field visibility settings updated.');
    }
    public function store(Request $request)
    {
        dd($request->all());
        $request->validate([
            'sub_project_id' => 'required|exists:sub_projects,id',
            'fieldVisibilities' => 'required|array',
            'fieldVisibilities.*' => 'boolean',
        ]);

        foreach ($request->fieldVisibilities as $field_name => $is_hidden) {
            SubProjectFieldVisibility::updateOrCreate(
                [
                    'sub_project_id' => $request->sub_project_id,
                    'field_name' => $field_name,
                ],
                [
                    'is_hidden' => $is_hidden,
                ]
            );
        }

        return redirect()->back()->with('message', 'Field visibility settings updated.');
    }


    /**
     * Bulk update the specified field visibilities in storage.
     */
    public function bulkUpdate(Request $request)
    {
        $updates = $request->input('updates');

        foreach ($updates as $update) {
            // Validate each update
            $validator = Validator::make($update, [
                'sub_project_id' => 'required|integer|exists:sub_projects,id',
                'field_name' => 'required|string',
                'is_hidden' => 'required|boolean',
                'update_existing' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            }

            // Update or create the field visibility
            SubProjectFieldVisibility::updateOrCreate(
                [
                    'sub_project_id' => $update['sub_project_id'],
                    'field_name' => $update['field_name'],
                ],
                [
                    'is_hidden' => $update['is_hidden'],
                ]
            );
        }

        return redirect()->route('field-visibility.index')->with('message', 'Field visibility updated successfully.');
    }


    /**
     * Remove the specified field visibility from storage.
     */
    public function destroy(SubProjectFieldVisibility $subProjectFieldVisibility)
    {
        $subProjectFieldVisibility->delete();

        return redirect()->back()->with('message', 'Field visibility removed successfully.');
    }
}
