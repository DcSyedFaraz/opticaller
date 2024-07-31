<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Toast; // Import the toast.js file

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::all();
        return inertia('Projects/Index', ['projects' => $projects]);
    }
    public function create()
    {
        return inertia('Projects/Create');
    }

    public function store(Request $request)
    {
        $project = Project::create($request->all());

        return redirect()->route('projects.index')->with('success', 'Project created successfully');
    }

    public function update(Request $request, Project $project)
    {
        $project->update($request->all());
        return redirect()->route('projects.index');
    }
    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully');
    }
}
