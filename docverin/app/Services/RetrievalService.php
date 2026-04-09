<?php

namespace App\Services;

use App\Models\DocumentChunk;
use App\Models\Workspace;

class RetrievalService
{
    public function __construct(
        protected EmbeddingService $embeddingService
    ) {
    }

    public function search(Workspace $workspace, string $query, int $limit = 3): array
    {
        $queryEmbedding = $this->embeddingService->embed($query, 'query');

        $chunks = DocumentChunk::query()
            ->whereHas('document', function ($queryBuilder) use ($workspace) {
                $queryBuilder->where('workspace_id', $workspace->id);
            })
            ->whereNotNull('embedding_json')
            ->with('document')
            ->get();

        $scored = [];

        foreach ($chunks as $chunk) {
            $embedding = $chunk->embedding_json;

            if (! is_array($embedding)) {
                continue;
            }

            $score = $this->cosineSimilarity($queryEmbedding, $embedding);

            $scored[] = [
                'chunk' => $chunk,
                'score' => $score,
            ];
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, $limit);
    }

    protected function cosineSimilarity(array $a, array $b): float
    {
        $length = min(count($a), count($b));

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < $length; $i++) {
            $dot += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }
}