<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Services\ChunkingService;
use App\Support\DocumentStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use App\Jobs\GenerateEmbeddingsJob;
use Throwable;

class ChunkDocumentJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $documentId
    ) {
    }

    public function handle(ChunkingService $chunkingService): void
    {
        $document = Document::findOrFail($this->documentId);

        $document->update([
            'status' => DocumentStatus::CHUNKING,
            'error_message' => null,
            'failed_at' => null,
        ]);

        try {
            $text = trim((string) $document->extracted_text);

            if ($text === '') {
                throw new \RuntimeException('Document has no extracted text to chunk.');
            }

            $chunks = $chunkingService->chunkText($text);

            if (empty($chunks)) {
                throw new \RuntimeException('Chunking returned no chunks.');
            }

            DocumentChunk::where('document_id', $document->id)->delete();

            foreach ($chunks as $chunk) {
                DocumentChunk::create([
                    'document_id' => $document->id,
                    'chunk_index' => $chunk['chunk_index'],
                    'content' => $chunk['content'],
                    'character_count' => $chunk['character_count'],
                ]);
            }

            $document->update([
                'status' => DocumentStatus::CHUNKED,
                'chunk_count' => count($chunks),
            ]);

            GenerateEmbeddingsJob::dispatch($document->id);
        } catch (Throwable $e) {
            Log::error('Document chunking failed', [
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