<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Workspace;
use App\Support\DocumentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Jobs\ExtractDocumentTextJob;

class DocumentController extends Controller
{
    public function store(Request $request, Workspace $workspace)
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:txt,md,pdf', 'max:20480'],
        ]);

        $file = $request->file('file');

        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs("documents/{$workspace->id}", $filename, 'local');

        Document::create([
            'workspace_id' => $workspace->id,
            'title' => $validated['title'] ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'status' => DocumentStatus::UPLOADED,
        ]);

        $document = Document::create([
            'workspace_id' => $workspace->id,
            'title' => $validated['title'] ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'status' => DocumentStatus::UPLOADED,
        ]);

        ExtractDocumentTextJob::dispatch($document->id);

        return redirect()->route('workspaces.show', $workspace);
    }
}