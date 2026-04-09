<?php

namespace App\Http\Controllers;

use App\Models\QaQuery;
use App\Models\QaSource;
use App\Models\Workspace;
use App\Services\AnswerGenerationService;
use App\Services\RetrievalService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AskController extends Controller
{
    public function show(Workspace $workspace): View
    {
        $queries = $workspace->qaQueries()
            ->latest()
            ->with(['sources.chunk.document'])
            ->get();

        return view('ask.show', [
            'workspace' => $workspace,
            'queries' => $queries,
        ]);
    }

    public function store(
        Request $request,
        Workspace $workspace,
        RetrievalService $retrievalService,
        AnswerGenerationService $answerGenerationService
    ): View {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:2000'],
        ]);

        $question = $validated['question'];

        $qaQuery = QaQuery::create([
            'workspace_id' => $workspace->id,
            'question' => $question,
            'status' => 'processing',
        ]);

        try {
            $results = $retrievalService->search($workspace, $question, 3);

            if (empty($results)) {
                $qaQuery->update([
                    'status' => 'failed',
                    'error_message' => 'No relevant chunks found.',
                ]);
            } else {
                foreach ($results as $result) {
                    QaSource::create([
                        'qa_query_id' => $qaQuery->id,
                        'document_chunk_id' => $result['chunk']->id,
                        'score' => $result['score'],
                    ]);
                }

                $answer = $answerGenerationService->generate($question, $results);

                $qaQuery->update([
                    'answer' => $answer,
                    'status' => 'completed',
                    'error_message' => null,
                ]);
            }
        } catch (\Throwable $e) {
            $qaQuery->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }

        $queries = $workspace->qaQueries()
            ->latest()
            ->with(['sources.chunk.document'])
            ->get();

        return view('ask.show', [
            'workspace' => $workspace,
            'queries' => $queries,
        ]);
    }
}