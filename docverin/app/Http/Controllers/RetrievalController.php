<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Services\RetrievalService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RetrievalController extends Controller
{
    public function show(Workspace $workspace): View
    {
        return view('retrieval.show', [
            'workspace' => $workspace,
            'results' => [],
            'query' => '',
        ]);
    }

    public function search(Request $request, Workspace $workspace, RetrievalService $retrievalService): View
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:1000'],
        ]);

        $query = $validated['query'];

        $results = $retrievalService->search($workspace, $query, 3);

        return view('retrieval.show', [
            'workspace' => $workspace,
            'results' => $results,
            'query' => $query,
        ]);
    }
}