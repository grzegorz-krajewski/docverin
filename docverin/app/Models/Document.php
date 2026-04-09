<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = [
        'workspace_id',
        'title',
        'original_filename',
        'file_path',
        'mime_type',
        'size_bytes',
        'status',
        'extracted_text',
        'character_count',
        'chunk_count',
        'indexed_at',
        'failed_at',
        'error_message',
    ];

    protected $casts = [
        'indexed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(DocumentChunk::class)->orderBy('chunk_index');
    }
}