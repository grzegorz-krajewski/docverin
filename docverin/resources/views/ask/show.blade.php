@php use Illuminate\Support\Str; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ask {{ $workspace->name }} - Docverin</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 40px auto; padding: 0 16px; }
        .card { border: 1px solid #ddd; border-radius: 12px; padding: 16px; margin-bottom: 16px; }
        textarea, button { padding: 10px 12px; font-size: 14px; }
        textarea { width: 100%; min-height: 100px; }
        button { cursor: pointer; }
        a { text-decoration: none; color: #0a58ca; }
        pre { white-space: pre-wrap; font-size: 12px; }
        .muted { color: #666; font-size: 12px; }
        .error { color: #b00020; }
    </style>
</head>
<body>
    <p><a href="{{ route('workspaces.show', $workspace) }}">← Back to workspace</a></p>

    <h1>Ask workspace</h1>
    <p>{{ $workspace->name }}</p>

    <div class="card">
        <form method="POST" action="{{ route('workspaces.ask.store', $workspace) }}">
            @csrf
            <div style="margin-bottom: 12px;">
                <label>Question</label><br>
                <textarea name="question" required placeholder="Ask something about the uploaded documents...">{{ old('question') }}</textarea>
            </div>
            <button type="submit">Ask</button>
        </form>

        @if ($errors->any())
            <div class="error" style="margin-top: 12px;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    @foreach ($queries as $query)
        <div class="card">
            <h2>Question</h2>
            <p>{{ $query->question }}</p>

            <h2>Answer</h2>
            @if ($query->status === 'completed')
                <p>{{ $query->answer }}</p>
            @elseif ($query->status === 'failed')
                <p class="error">{{ $query->error_message }}</p>
            @else
                <p class="muted">Processing...</p>
            @endif

            @if ($query->sources->isNotEmpty())
                <h3>Sources</h3>
                @foreach ($query->sources as $source)
                    <div style="border-top: 1px solid #eee; padding: 12px 0;">
                        <div class="muted">
                            Score: {{ number_format((float) $source->score, 4) }} |
                            Document: {{ $source->chunk->document->title ?? 'Unknown' }} |
                            Chunk #{{ $source->chunk->chunk_index ?? '?' }}
                        </div>
                        <pre>{{ \Illuminate\Support\Str::limit($source->chunk->content ?? '', 800) }}</pre>
                    </div>
                @endforeach
            @endif
        </div>
    @endforeach
</body>
</html>