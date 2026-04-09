<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QaSource extends Model
{
    protected $fillable = [
        'qa_query_id',
        'document_chunk_id',
        'score',
    ];

    public function qaQuery(): BelongsTo
    {
        return $this->belongsTo(QaQuery::class, 'qa_query_id');
    }

    public function chunk(): BelongsTo
    {
        return $this->belongsTo(DocumentChunk::class, 'document_chunk_id');
    }
}