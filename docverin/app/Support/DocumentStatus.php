<?php

namespace App\Support;

class DocumentStatus
{
    public const UPLOADED = 'uploaded';
    public const EXTRACTING = 'extracting';
    public const EXTRACTED = 'extracted';
    public const CHUNKING = 'chunking';
    public const CHUNKED = 'chunked';
    public const EMBEDDING = 'embedding';
    public const EMBEDDED = 'embedded';
    public const INDEXED = 'indexed';
    public const FAILED = 'failed';
}