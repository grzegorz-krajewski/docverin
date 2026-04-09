<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function index(): View
    {
        $workspaces = Workspace::latest()->get();

        return view('workspaces.index', compact('workspaces'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $workspace = Workspace::create([
            'name' => $validated['name'],
        ]);

        return redirect()->route('workspaces.show', $workspace);
    }

    public function show(Workspace $workspace): View
    {
        $workspace->load([
            'documents' => fn ($query) => $query->latest()->with('chunks'),
        ]);

        return view('workspaces.show', compact('workspace'));
    }
}