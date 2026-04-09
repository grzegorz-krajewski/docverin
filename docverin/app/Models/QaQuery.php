<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QaQuery extends Model
{
    protected $fillable = [
        'workspace_id',
        'question',
        'answer',
        'status',
        'error_message',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function sources(): HasMany
    {
        return $this->hasMany(QaSource::class);
    }
}