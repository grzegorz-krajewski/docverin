<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\EmbeddingService;
use App\Support\DocumentStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateEmbeddingsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $documentId
    ) {
    }

    public function handle(EmbeddingService $embeddingService): void
    {
        $document = Document::with('chunks')->findOrFail($this->documentId);

        $document->update([
            'status' => DocumentStatus::EMBEDDING,
            'error_message' => null,
            'failed_at' => null,
        ]);

        try {
            if ($document->chunks->isEmpty()) {
                throw new \RuntimeException('Document has no chunks to embed.');
            }

            $chunks = $document->chunks->sortBy('chunk_index')->values();

            $texts = $chunks->pluck('content')->all();

            $embeddings = $embeddingService->embedMany($texts, 'document');

            if (count($embeddings) !== $chunks->count()) {
                throw new \RuntimeException('Embedding count does not match chunk count.');
            }

            foreach ($chunks as $index => $chunk) {
                $chunk->update([
                    'embedding_model' => config('services.voyage.model', 'voyage-3.5-lite'),
                    'embedding_json' => $embeddings[$index],
                ]);
            }

            $document->update([
                'status' => DocumentStatus::EMBEDDED,
            ]);
        } catch (Throwable $e) {
            Log::error('Embedding generation failed', [
                'document_id' => $document->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $document->update([
                'status' => DocumentStatus::FAILED,
                'failed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}