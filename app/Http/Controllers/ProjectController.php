<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Toast;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Storage;// Import the toast.js file

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::all();
        return inertia('Projects/Index', ['projects' => $projects]);
    }
    public function assign()
    {
        $users = User::select('name', 'id')->get();
        $subprojects = SubProject::with('users', 'projects')->get();

        return inertia('Projects/assignToUsers', ['users' => $users, 'subProjects' => $subprojects]);
    }
    public function create()
    {
        $projects = Project::select('title', 'id')->get();
        $subprojects = SubProject::with('projects')->get();

        return inertia('Projects/subproject', ['projects' => $projects, 'subprojects' => $subprojects]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'color' => 'required',
        ]);

        Project::create($validatedData);

        return redirect()->route('projects.index')->with('success', 'Project created successfully');
    }
    public function subprojects(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'priority' => 'nullable',
            'description' => 'required|string',
            'project_id' => 'required|exists:projects,id',
            'pdf' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        $data = $request->only(['title', 'description', 'project_id', 'priority']);

        if ($request->hasFile('pdf')) {
            $path = $request->file('pdf')->store('pdfs', 'public');
            $data['pdf_path'] = $path;
        }

        $subProject = SubProject::create($data);

        return redirect()->route('projects.create')->with('success', 'Project created successfully');
    }
    public function subprojectsUpdate(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'priority' => 'nullable',
            'description' => 'required|string',
            'project_id' => 'required|exists:projects,id',
            'pdf' => 'nullable|file|mimes:pdf|max:2048',

        ]);
        $subproject = SubProject::findOrFail($id);
        // dd($request->all(), $project);
        $data = $request->only(['title', 'description', 'project_id', 'priority']);

        if ($request->hasFile('pdf')) {
            // Delete old PDF if exists
            if ($subproject->pdf_path) {
                Storage::disk('public')->delete($subproject->pdf_path);
            }

            $path = $request->file('pdf')->store('pdfs', 'public');
            $data['pdf_path'] = $path;
        }

        $subproject->update($data);
        return redirect()->route('projects.create');
    }
    public function assignUsers(Request $request, SubProject $subProject)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1|exists:users,id',
        ], [
            'user_ids.required' => 'Please select at least one user',
            'user_ids.array' => 'User IDs must be an array',
            'user_ids.min' => 'Please select at least one user',
            'user_ids.exists' => 'One or more user IDs do not exist',
        ]);

        $subProject->users()->sync($request->input('user_ids'));

        $subProject->load('users', 'projects');

        // Return the updated subProject with its users as JSON
        return response()->json([
            'message' => 'Users assigned successfully',
            'subProject' => $subProject,
        ]);
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $errorMessages = [];
        foreach ($errors->all() as $error) {
            $errorMessages[] = ['severity' => 'error', 'summary' => 'Error', 'detail' => $error, 'life' => 3000];
        }
        return response()->json(['errors' => $errorMessages], 422);
    }

    public function update(Request $request, Project $project)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'color' => 'required',
        ]);

        $project->update($validatedData);
        return redirect()->route('projects.index');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.create')->with('success', 'Sub Project deleted successfully');
    }
    public function subprojectsDelete($id)
    {
        $subproject = SubProject::findOrFail($id);
        if ($subproject->pdf_path) {
            Storage::disk('public')->delete($subproject->pdf_path);
        }
        $subproject->delete();
        return redirect()->route('projects.create')->with('success', 'Project deleted successfully');
    }
}
