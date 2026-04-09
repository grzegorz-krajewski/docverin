<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\DocumentParserService;
use App\Support\DocumentStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use App\Jobs\ChunkDocumentJob;
use Throwable;

class ExtractDocumentTextJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $documentId
    ) {
    }

    public function handle(DocumentParserService $parser): void
    {
        $document = Document::findOrFail($this->documentId);

        $document->update([
            'status' => DocumentStatus::EXTRACTING,
            'error_message' => null,
            'failed_at' => null,
        ]);

        try {
            $text = $parser->extract($document);

            $document->update([
                'status' => DocumentStatus::EXTRACTED,
                'extracted_text' => $text,
                'character_count' => mb_strlen($text),
            ]);

            ChunkDocumentJob::dispatch($document->id);
        } catch (Throwable $e) {
            Log::error('Document extraction failed', [
                'document_id' => $document->id,
                'file_path' => $document->file_path,
                'mime_type' => $document->mime_type,
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