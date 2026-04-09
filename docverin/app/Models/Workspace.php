<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Workspace extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    protected static function booted(): void
    {
        static::creating(function (Workspace $workspace) {
            if (blank($workspace->slug)) {
                $workspace->slug = Str::slug($workspace->name) . '-' . Str::lower(Str::random(6));
            }
        });
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function qaQueries(): HasMany
    {
        return $this->hasMany(QaQuery::class);
    }
}