<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $workspace->name }} - Docverin</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 40px auto; padding: 0 16px; }
        .card { border: 1px solid #ddd; border-radius: 12px; padding: 16px; margin-bottom: 16px; }
        input, button { padding: 10px 12px; font-size: 14px; }
        input[type="text"] { width: 100%; max-width: 420px; }
        button { cursor: pointer; }
        a { text-decoration: none; color: #0a58ca; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
    </style>
</head>
<body>
    <p><a href="{{ route('workspaces.index') }}">← Back to workspaces</a></p>

    <h1>{{ $workspace->name }}</h1>
    <p>{{ $workspace->slug }}</p>
    <p>
        <a href="{{ route('workspaces.ask.show', $workspace) }}">Ask this workspace →</a>
    </p>
    
    <div class="card">
        <h2>Upload document</h2>

        <form method="POST" action="{{ route('workspaces.documents.store', $workspace) }}" enctype="multipart/form-data">
            @csrf

            <div style="margin-bottom: 12px;">
                <label>Title</label><br>
                <input type="text" name="title" placeholder="Optional custom title">
            </div>

            <div style="margin-bottom: 12px;">
                <label>File</label><br>
                <input type="file" name="file" required>
            </div>

            <button type="submit">Upload</button>
        </form>

        @if ($errors->any())
            <div style="margin-top: 12px; color: #b00020;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="card">
        <h2>Documents</h2>

        @if ($workspace->documents->isEmpty())
            <p>No documents uploaded yet.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Original file</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Size</th>
                        <th>Chunks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($workspace->documents as $document)
                        <tr>
                            <td>{{ $document->title }}</td>
                            <td>{{ $document->original_filename }}</td>
                            <td>{{ $document->mime_type }}</td>
                            <td>{{ $document->status }}</td>
                            <td>{{ $document->size_bytes }} bytes</td>
                            <td>{{ $document->chunk_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @foreach ($workspace->documents as $document)
        @if ($document->chunks->isNotEmpty())
            <div class="card">
                <h3>{{ $document->title }} — chunks ({{ $document->chunks->count() }})</h3>

                @foreach ($document->chunks as $chunk)
                    <div style="border-top: 1px solid #eee; padding: 12px 0;">
                        <div style="font-size: 12px; color: #666; margin-bottom: 6px;">
                            Chunk #{{ $chunk->chunk_index }} · {{ $chunk->character_count }} chars
                        </div>
                        <pre style="white-space: pre-wrap; font-size: 12px;">{{ $chunk->content }}</pre>
                    </div>
                @endforeach
            </div>
        @endif
    @endforeach
</body>
</html>