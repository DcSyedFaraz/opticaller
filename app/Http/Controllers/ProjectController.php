<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Toast;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;// Import the toast.js file

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::all();
        return inertia('Projects/Index', ['projects' => $projects]);
    }
    public function assign()
    {
        $users = User::role('user')->select('name', 'id')->get();
        $subprojects = SubProject::with('users')->get();

        return inertia('Projects/assignToUsers', ['users' => $users, 'subProjects' => $subprojects]);
    }
    public function create()
    {
        $projects = Project::select('title', 'id')->get();
        $subprojects = SubProject::all();

        return inertia('Projects/subproject', ['projects' => $projects, 'subprojects' => $subprojects]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        Project::create($validatedData);

        return redirect()->route('projects.index')->with('success', 'Project created successfully');
    }
    public function subprojects(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'project_id' => 'required|exists:projects,id',
        ]);

        SubProject::create($validatedData);

        return redirect()->route('projects.index')->with('success', 'Project created successfully');
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

        return redirect()->back()->with('success', 'Users assigned successfully');
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
        ]);

        $project->update($validatedData);
        return redirect()->route('projects.index');
    }
    public function subprojectsUpdate(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'project_id' => 'required|exists:projects,id',

        ]);
        $project = SubProject::findOrFail($id);
        // dd($request->all(), $project);
        $project->update($validatedData);
        return redirect()->route('projects.create');
    }
    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Sub Project deleted successfully');
    }
    public function subprojectsDelete(SubProject $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully');
    }
}
