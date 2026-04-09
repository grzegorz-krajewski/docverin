<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ask {{ $workspace->name }} - Docverin</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 40px auto; padding: 0 16px; }
        .card { border: 1px solid #ddd; border-radius: 12px; padding: 16px; margin-bottom: 16px; }
        input, button { padding: 10px 12px; font-size: 14px; }
        input[type="text"] { width: 100%; max-width: 700px; }
        button { cursor: pointer; }
        a { text-decoration: none; color: #0a58ca; }
        pre { white-space: pre-wrap; font-size: 12px; }
    </style>
</head>
<body>
    <p><a href="{{ route('workspaces.show', $workspace) }}">← Back to workspace</a></p>

    <h1>Ask workspace</h1>
    <p>{{ $workspace->name }}</p>

    <div class="card">
        <form method="POST" action="{{ route('workspaces.ask.search', $workspace) }}">
            @csrf
            <div style="margin-bottom: 12px;">
                <label>Question</label><br>
                <input type="text" name="query" value="{{ $query }}" placeholder="What is this document about?" required>
            </div>
            <button type="submit">Search relevant chunks</button>
        </form>
    </div>

    @if (!empty($results))
        <div class="card">
            <h2>Top results</h2>

            @foreach ($results as $result)
                <div style="border-top: 1px solid #eee; padding: 12px 0;">
                    <div style="font-size: 12px; color: #666; margin-bottom: 6px;">
                        Score: {{ number_format($result['score'], 4) }} |
                        Document: {{ $result['chunk']->document->title }} |
                        Chunk #{{ $result['chunk']->chunk_index }}
                    </div>
                    <pre>{{ $result['chunk']->content }}</pre>
                </div>
            @endforeach
        </div>
    @endif
</body>
</html>