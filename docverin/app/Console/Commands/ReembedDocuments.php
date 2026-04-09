<?php

namespace App\Console\Commands;

use App\Jobs\GenerateEmbeddingsJob;
use App\Models\Document;
use Illuminate\Console\Command;

class ReembedDocuments extends Command
{
    protected $signature = 'docverin:reembed {documentId?}';
    protected $description = 'Regenerate embeddings for one document or all chunked documents';

    public function handle(): int
    {
        $documentId = $this->argument('documentId');

        $query = Document::query()->where('chunk_count', '>', 0);

        if ($documentId) {
            $query->where('id', $documentId);
        }

        $documents = $query->get();

        if ($documents->isEmpty()) {
            $this->warn('No documents found to re-embed.');
            return self::SUCCESS;
        }

        foreach ($documents as $document) {
            GenerateEmbeddingsJob::dispatch($document->id);
            $this->info("Queued embeddings for document #{$document->id}");
        }

        return self::SUCCESS;
    }
}